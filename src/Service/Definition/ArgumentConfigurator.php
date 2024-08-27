<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Temkaa\SimpleContainer\Attribute\Bind\Parameter;
use Temkaa\SimpleContainer\Attribute\Bind\Tagged;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Factory\Definition\InterfaceFactory;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Reference\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Model\Reference\Deferred\InterfaceReference;
use Temkaa\SimpleContainer\Model\Reference\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Model\Reference\Reference;
use Temkaa\SimpleContainer\Model\Reference\ReferenceInterface;
use Temkaa\SimpleContainer\Util\ExpressionParser;
use Temkaa\SimpleContainer\Util\Extractor\AttributeExtractor;
use Temkaa\SimpleContainer\Util\TypeCaster;
use Temkaa\SimpleContainer\Validator\ArgumentValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class ArgumentConfigurator
{
    private BaseConfigurator $definitionConfigurator;

    private ExpressionParser $expressionParser;

    public function __construct(BaseConfigurator $definitionConfigurator)
    {
        $this->definitionConfigurator = $definitionConfigurator;
        $this->expressionParser = new ExpressionParser();
    }

    /**
     * @param Config              $config
     * @param Bag                 $definitions
     * @param ReflectionParameter $argument
     * @param ClassDefinition     $definition
     * @param class-string        $id
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function configureArgument(
        Config $config,
        Bag $definitions,
        ReflectionParameter $argument,
        ClassDefinition $definition,
        string $id,
    ): mixed {
        (new ArgumentValidator())->validate($argument, $id);

        $decorates = $definition->getDecorates();
        if ($decorates && $decorates->getSignature() === $argument->getName()) {
            return new DecoratorReference(
                $decorates->getId(), $decorates->getPriority(), $decorates->getSignature(),
            );
        }

        if ($configuredArgument = $this->configureTaggedArgument($config, $argument, $id)) {
            return $configuredArgument;
        }

        [
            'value'    => $configuredArgument,
            'resolved' => $resolved,
        ] = $this->configureBuiltinArgument($config, $argument, $id);

        if ($resolved) {
            return $configuredArgument;
        }

        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        /** @var class-string $entryId */
        $entryId = $argumentType->getName();

        if ($configuredArgument = $this->configureInterfaceArgument($config, $definitions, $entryId)) {
            return $configuredArgument;
        }

        if (!$definitions->has($entryId)) {
            $this->definitionConfigurator->configureDefinition($entryId);
        }

        return new Reference($entryId);
    }

    /**
     * @param Config              $config
     * @param ReflectionParameter $argument
     * @param class-string        $id
     *
     * @return array{value: mixed, resolved: boolean}
     *
     * @throws ContainerExceptionInterface
     */
    private function configureBuiltinArgument(Config $config, ReflectionParameter $argument, string $id): array
    {
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();

        if ($argumentAttributes = $argument->getAttributes(Parameter::class)) {
            $expression = AttributeExtractor::extractParameters($argumentAttributes, parameter: 'expression')[0];

            return [
                'value'    => TypeCaster::cast(
                    $this->expressionParser->parse($expression),
                    $argumentType->getName(),
                ),
                'resolved' => true,
            ];
        }

        if (!$argumentType->isBuiltin()) {
            return ['value' => null, 'resolved' => false];
        }

        $argumentName = $argument->getName();

        $boundVariableValue = $this->getBoundVariableValue($config, $argumentName, $id);
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if (!$boundVariableValue && !$argumentType->allowsNull()) {
            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot instantiate entry "%s" with argument "%s::%s".',
                    $id,
                    $argumentName,
                    $argumentType->getName(),
                ),
            );
        }

        /** @psalm-suppress MixedAssignment, RiskyTruthyFalsyComparison */
        $resolvedValue = $boundVariableValue
            ? TypeCaster::cast(
                $this->expressionParser->parse($boundVariableValue),
                $argumentType->getName(),
            )
            : null;

        return ['value' => $resolvedValue, 'resolved' => true];
    }

    /**
     * @param Config       $config
     * @param Bag          $definitions
     * @param class-string $entryId
     *
     * @return ReferenceInterface|null
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function configureInterfaceArgument(Config $config, Bag $definitions, string $entryId): ?ReferenceInterface
    {
        try {
            $dependencyReflection = new ReflectionClass($entryId);
        } catch (ReflectionException) {
            throw new ClassNotFoundException($entryId);
        }

        if (!$dependencyReflection->isInterface()) {
            return null;
        }

        $interfaceName = $dependencyReflection->getName();
        if (!$config->hasBoundInterface($interfaceName)) {
            return new InterfaceReference($interfaceName);
        }

        $interfaceImplementation = $config->getBoundInterfaceImplementation($interfaceName);
        $definitions->add(
            InterfaceFactory::create(
                $interfaceName,
                $interfaceImplementation,
            ),
        );

        $this->definitionConfigurator->configureDefinition($interfaceImplementation);

        return new Reference($interfaceName);
    }

    /**
     * @param Config              $config
     * @param ReflectionParameter $argument
     * @param class-string        $id
     *
     * @return TaggedReference|null
     */
    private function configureTaggedArgument(
        Config $config,
        ReflectionParameter $argument,
        string $id,
    ): ?TaggedReference {
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();

        if ($argumentAttributes = $argument->getAttributes(Tagged::class)) {
            $boundTagName = AttributeExtractor::extractParameters($argumentAttributes, parameter: 'tag')[0];

            if (!in_array($argumentType->getName(), ['iterable', 'array'])) {
                throw new UnresolvableArgumentException(
                    sprintf(
                        'Cannot instantiate entry "%s" with tagged argument "%s::%s" as it\'s type is neither "array" or "iterable".',
                        $id,
                        $argument->getName(),
                        $argumentType->getName(),
                    ),
                );
            }

            return new TaggedReference(tag: $boundTagName);
        }

        if (!$argumentType->isBuiltin()) {
            return null;
        }

        $boundVariableValue = $this->getBoundVariableValue($config, $argument->getName(), $id);
        /** @psalm-suppress PossiblyNullArgument, RiskyTruthyFalsyComparison */
        if ($boundVariableValue && str_starts_with($boundVariableValue, '!tagged')) {
            $tag = trim(str_replace('!tagged', '', $boundVariableValue));

            return new TaggedReference($tag);
        }

        return null;
    }

    /**
     * @param Config       $config
     * @param string       $argumentName
     * @param class-string $id
     *
     * @return string|null
     */
    private function getBoundVariableValue(Config $config, string $argumentName, string $id): ?string
    {
        $boundClassInfo = $config->getBoundedClasses()[$id] ?? null;
        $classBoundVars = $boundClassInfo?->getBoundedVariables() ?? [];
        $globalBoundVars = $config->getBoundedVariables();

        return $classBoundVars[$argumentName] ?? $globalBoundVars[$argumentName] ?? null;
    }
}
