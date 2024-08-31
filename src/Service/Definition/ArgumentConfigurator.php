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
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Temkaa\SimpleContainer\Model\Config\Factory;
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
use Temkaa\SimpleContainer\Validator\Argument\ExpressionTypeCompatibilityValidator;
use Temkaa\SimpleContainer\Validator\ArgumentValidator;
use UnitEnum;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class ArgumentConfigurator
{
    private Configurator $definitionConfigurator;

    private ExpressionParser $expressionParser;

    public function __construct(Configurator $definitionConfigurator)
    {
        $this->definitionConfigurator = $definitionConfigurator;
        $this->expressionParser = new ExpressionParser();
    }

    /**
     * @param Config               $config
     * @param Bag                  $definitions
     * @param ReflectionParameter  $argument
     * @param ClassDefinition|null $definition
     * @param class-string         $id
     * @param bool                 $isConstructor
     * @param Decorator|null       $decorates
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
        ?ClassDefinition $definition,
        string $id,
        ?Factory $factory,
        ?Decorator $decorates,
    ): mixed {
        (new ArgumentValidator())->validate($argument, $id);

        if ($decorates && $decorates->getSignature() === $argument->getName()) {
            return new DecoratorReference(
                $decorates->getId(), $decorates->getPriority(), $decorates->getSignature(),
            );
        }

        if ($configuredArgument = $this->configureTaggedArgument($config, $argument, $id, $factory)) {
            return $configuredArgument;
        }

        [
            'value'    => $configuredArgument,
            'resolved' => $resolved,
        ] = $this->configureNonObjectArgument($config, $argument, $id, $factory);

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
     * @param Factory|null        $factory
     *
     * @return array{value: mixed, resolved: boolean}
     *
     * @throws ContainerExceptionInterface
     */
    private function configureNonObjectArgument(
        Config $config,
        ReflectionParameter $argument,
        string $id,
        ?Factory $factory,
    ): array {
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        $argumentTypeName = $argumentType->getName();
        $argumentName = $argument->getName();

        $argumentAttributes = AttributeExtractor::extractParameters(
            $argument->getAttributes(Parameter::class),
            parameter: 'expression',
        );

        $configExpression = $this->getBoundVariableValue($config, $argumentName, $id, $factory);
        $argumentExpression = $argumentAttributes ? current($argumentAttributes) : null;

        if ($configExpression === null && $argumentExpression === null) {
            if (!$argumentType->isBuiltin()) {
                return ['value' => null, 'resolved' => false];
            }

            if ($argumentType->allowsNull()) {
                return ['value' => null, 'resolved' => true];
            }

            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot instantiate entry "%s" with argument "%s::%s".',
                    $id,
                    $argumentName,
                    $argumentTypeName,
                ),
            );
        }

        $expression = $configExpression ?? $argumentExpression;

        (new ExpressionTypeCompatibilityValidator())->validate($expression, $argument, $id);

        return [
            'value'    => $expression instanceof UnitEnum
                ? $expression
                : TypeCaster::cast($this->expressionParser->parse($expression), $argumentTypeName),
            'resolved' => true,
        ];
    }

    /**
     * @param Config              $config
     * @param ReflectionParameter $argument
     * @param class-string        $id
     * @param Factory|null        $factory
     *
     * @return TaggedReference|null
     */
    private function configureTaggedArgument(
        Config $config,
        ReflectionParameter $argument,
        string $id,
        ?Factory $factory,
    ): ?TaggedReference {
        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();
        $argumentName = $argument->getName();

        if (!$argumentType->isBuiltin()) {
            return null;
        }

        $argumentAttributes = AttributeExtractor::extractParameters(
            $argument->getAttributes(Tagged::class),
            parameter: 'tag',
        );

        $configExpression = $this->getBoundVariableValue($config, $argumentName, $id, $factory);
        $argumentExpression = $argumentAttributes ? current($argumentAttributes) : null;

        if (!$configExpression && !$argumentExpression) {
            return null;
        }

        if ($configExpression && (!is_string($configExpression) || !str_starts_with($configExpression, '!tagged'))) {
            return null;
        }

        if (!in_array($argumentType->getName(), ['iterable', 'array'])) {
            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot instantiate entry "%s" with tagged argument "%s::%s" as it\'s type is neither "array" or "iterable".',
                    $id,
                    $argumentName,
                    $argumentType->getName(),
                ),
            );
        }

        return new TaggedReference(
            $configExpression ? trim(str_replace('!tagged', '', $configExpression)) : $argumentExpression,
        );
    }

    /**
     * @param Config       $config
     * @param string       $argumentName
     * @param class-string $id
     * @param Factory|null $factory
     *
     * @return null|string|UnitEnum
     */
    private function getBoundVariableValue(
        Config $config,
        string $argumentName,
        string $id,
        ?Factory $factory,
    ): null|string|UnitEnum {
        $classBinding = $config->getBoundedClass($id);
        $classBoundVars = $classBinding?->getBoundedVariables() ?? [];
        $classFactoryBindings = $factory?->getBoundedVariables() ?? [];

        $globalBoundVars = $config->getBoundedVariables();

        return $factory
            ? $classFactoryBindings[$argumentName] ?? $globalBoundVars[$argumentName] ?? null
            : $classBoundVars[$argumentName] ?? $globalBoundVars[$argumentName] ?? null;
    }
}
