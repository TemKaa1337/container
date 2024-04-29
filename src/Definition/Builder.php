<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use Temkaa\SimpleCollections\Collection;
use Temkaa\SimpleCollections\Model\Sort\ByCallback;
use Temkaa\SimpleContainer\Attribute\Alias;
use Temkaa\SimpleContainer\Attribute\Autowire;
use Temkaa\SimpleContainer\Attribute\Bind\Parameter;
use Temkaa\SimpleContainer\Attribute\Bind\Tagged;
use Temkaa\SimpleContainer\Attribute\Decorates;
use Temkaa\SimpleContainer\Attribute\Tag;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\NonAutowirableClassException;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Factory\Definition\DecoratorFactory;
use Temkaa\SimpleContainer\Model\Container\Config;
use Temkaa\SimpleContainer\Model\Definition;
use Temkaa\SimpleContainer\Model\Definition\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Model\Definition\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Model\Definition\Reference;
use Temkaa\SimpleContainer\Util\AttributeExtractor;
use Temkaa\SimpleContainer\Util\ExpressionParser;
use Temkaa\SimpleContainer\Util\TypeCaster;
use Temkaa\SimpleContainer\Validator\ArgumentValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
final class Builder
{
    /**
     * @var Config[] $configs
     */
    private readonly array $configs;

    private DecoratorFactory $decoratorFactory;

    /**
     * @var Definition[]
     */
    private array $definitions = [];

    /**
     * @var array<class-string, true>
     */
    private array $definitionsBuilding = [];

    private ExpressionParser $expressionParser;

    private Config $resolvingConfig;

    public function __construct(array $configs)
    {
        $this->configs = $configs;
        $this->decoratorFactory = new DecoratorFactory();
        $this->expressionParser = new ExpressionParser();
    }

    /**
     * @return Definition[]
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function build(): array
    {
        foreach ($this->configs as $config) {
            $this->resolvingConfig = $config;

            foreach ($config->getIncludedClasses() as $class) {
                $this->buildDefinition($class, failIfUninstantiable: false);
            }
        }

        $this->updateDecoratorReferences();

        return $this->definitions;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param class-string $id
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function buildArgument(ReflectionParameter $argument, Definition $definition, string $id): mixed
    {
        (new ArgumentValidator())->validate($argument, $id);

        // needed in order to suppress psalm undefined method messages
        /** @var ReflectionType&ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();

        if (!$argumentType) {
            throw new UnresolvableArgumentException(
                sprintf(
                    'Cannot resolve argument "%s" in "%s" because of missing type.',
                    $argument->getName(),
                    $id,
                ),
            );
        }

        $decorates = $definition->getDecorates();
        if ($decorates && $decorates->getSignature() === $argument->getName()) {
            return new DecoratorReference($decorates->getId(), $decorates->getPriority(), $decorates->getSignature());
        }

        if ($argumentAttributes = $argument->getAttributes(Tagged::class)) {
            $boundTagName = AttributeExtractor::extractParameters($argumentAttributes, parameter: 'tag')[0];

            if (!$argumentType->isBuiltin() || !in_array($argumentType->getName(), ['iterable', 'array'])) {
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

        if ($argumentAttributes = $argument->getAttributes(Parameter::class)) {
            $expression = AttributeExtractor::extractParameters($argumentAttributes, parameter: 'expression')[0];

            $parsedValue = $this->expressionParser->parse($expression);

            return TypeCaster::cast($parsedValue, $argumentType->getName());
        }

        if ($argumentType->isBuiltin()) {
            $argumentName = $argument->getName();

            $classBoundVars = $this->resolvingConfig->getClassBoundVariables($id);
            $globalBoundVars = $this->resolvingConfig->getGlobalBoundVariables();

            $hasBoundVariable = $classBoundVars[$argumentName] ?? $globalBoundVars[$argumentName] ?? false;
            $boundVariableValue = $classBoundVars[$argumentName] ?? $globalBoundVars[$argumentName] ?? null;

            if ($hasBoundVariable && str_starts_with((string) $boundVariableValue, '!tagged')) {
                /** @psalm-suppress PossiblyNullArgument */
                $tag = trim(str_replace('!tagged', '', $boundVariableValue));

                $resolvedValue = new TaggedReference($tag);
            } else {
                if (!$hasBoundVariable && !$argumentType->allowsNull()) {
                    throw new UnresolvableArgumentException(
                        sprintf(
                            'Cannot instantiate entry "%s" with argument "%s::%s".',
                            $id,
                            $argumentName,
                            $argumentType->getName(),
                        ),
                    );
                }

                $resolvedValue = $argumentType->allowsNull() && !$hasBoundVariable
                    ? null
                    : TypeCaster::cast($boundVariableValue, $argumentType->getName());
            }

            return $resolvedValue;
        }

        /** @var class-string $entryId */
        $entryId = $argumentType->getName();

        $dependencyReflection = new ReflectionClass($entryId);
        if ($dependencyReflection->isInterface()) {
            $interfaceImplementation = $this->resolvingConfig->getInterfaceImplementation(
                $dependencyReflection->getName(),
            );

            $this->buildDefinition($interfaceImplementation);

            $this->definitions[$dependencyReflection->getName()] = $this->definitions[$interfaceImplementation];

            return new Reference($interfaceImplementation);
        }

        if (!isset($this->definitions[$entryId])) {
            $this->buildDefinition($entryId);
        }

        return new Reference($entryId);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param class-string $id
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function buildDefinition(string $id, bool $failIfUninstantiable = true): void
    {
        if (isset($this->definitions[$id])) {
            return;
        }

        if ($this->isDefinitionBuilding($id)) {
            throw new CircularReferenceException($id, array_keys($this->definitionsBuilding));
        }

        $this->setBuilding($id, isBuilding: true);

        try {
            $reflection = new ReflectionClass($id);
        } catch (ReflectionException) {
            throw new ClassNotFoundException($id);
        }

        if ($reflection->isInternal()) {
            throw new UninstantiableEntryException(sprintf('Cannot resolve internal entry "%s".', $id));
        }

        if (!$reflection->isInstantiable()) {
            if (!$failIfUninstantiable) {
                return;
            }

            throw new UninstantiableEntryException(sprintf('Cannot instantiate entry with id "%s".', $id));
        }

        if (in_array($id, $this->resolvingConfig->getExcludedClasses(), strict: true)) {
            throw new NonAutowirableClassException(
                sprintf('Cannot autowire class "%s" as it is in "exclude" config parameter.', $id),
            );
        }

        $definition = (new Definition())->setId($id);

        if ($this->resolvingConfig->hasClassSingleton($id)) {
            $definition->setIsSingleton($this->resolvingConfig->getClassSingleton($id));
        }

        if ($autowireTags = $reflection->getAttributes(Autowire::class)) {
            $isSingleton = AttributeExtractor::extract($autowireTags, index: 0)->singleton;
            $definition->setIsSingleton($isSingleton);
        }

        $isNonAutowirable = AttributeExtractor::hasParameterByValue($autowireTags, parameter: 'load', value: false);
        if ($isNonAutowirable) {
            if (!$failIfUninstantiable) {
                return;
            }

            throw new NonAutowirableClassException(
                sprintf('Class "%s" has NonAutowirable attribute and cannot be autowired.', $id),
            );
        }

        $this->populateDefinition($definition, $reflection);

        if (!$constructor = $reflection->getConstructor()) {
            $this->setBuilding($id, isBuilding: false);

            $this->definitions[$id] = $definition;

            return;
        }

        $arguments = $constructor->getParameters();
        foreach ($arguments as $argument) {
            $definition->addArgument($this->buildArgument($argument, $definition, $id));
        }

        $this->definitions[$id] = $definition;

        $this->setBuilding($id, isBuilding: false);
    }

    /**
     * @param class-string $id
     */
    private function isDefinitionBuilding(string $id): bool
    {
        return $this->definitionsBuilding[$id] ?? false;
    }

    /**
     * @throws ContainerExceptionInterface
     */
    private function populateDefinition(Definition $definition, ReflectionClass $reflection): void
    {
        $classTags = $reflection->getAttributes(Tag::class);
        $classAliases = $reflection->getAttributes(Alias::class);

        $definition
            ->addTags($this->resolvingConfig->getClassTags($reflection->getName()))
            ->addTags(AttributeExtractor::extractParameters($classTags, 'name'))
            ->addAliases(AttributeExtractor::extractParameters($classAliases, 'name'));

        $interfaces = $reflection->getInterfaces();
        $definition->addImplements(array_keys($interfaces));
        foreach ($interfaces as $interface) {
            $interfaceName = $interface->getName();

            if (
                $this->resolvingConfig->hasImplementation($interfaceName)
                && $this->resolvingConfig->getInterfaceImplementation($interfaceName) === $reflection->getName()
            ) {
                $definition->addAlias($interfaceName);

                $this->definitions[$interfaceName] = $definition;
            }

            $interfaceTags = $interface->getAttributes(Tag::class);
            $definition->addTags(AttributeExtractor::extractParameters($interfaceTags, 'name'));
        }

        $decoratesAttribute = $reflection->getAttributes(Decorates::class);
        $decoratesConfig = $this->resolvingConfig->getDecorator($definition->getId());
        if ($decoratesAttribute || $decoratesConfig) {
            $definition->setDecorates(
                $decoratesAttribute
                    ? $this->decoratorFactory->createFromReflection(current($decoratesAttribute))
                    : $decoratesConfig,
            );
        }
    }

    /**
     * @param class-string $id
     */
    private function setBuilding(string $id, bool $isBuilding): void
    {
        if ($isBuilding) {
            $this->definitionsBuilding[$id] = true;
        } else {
            unset($this->definitionsBuilding[$id]);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function updateDecoratorReferences(): void
    {
        /** @var array<class-string, Definition[]> $decorators */
        $decorators = [];

        foreach ($this->definitions as $definition) {
            if ($decorates = $definition->getDecorates()) {
                $decorators[$decorates->getId()] ??= [];
                $decorators[$decorates->getId()][] = $definition;
            }
        }

        foreach ($decorators as $id => $definitions) {
            /** @var Definition[] $decoratorDefinitions */
            /** @psalm-suppress PossiblyNullReference */
            $decoratorDefinitions = (new Collection($definitions))
                ->sort(
                    new ByCallback(
                        static fn (Definition $definition): int => $definition->getDecorates()->getPriority(),
                    ),
                )
                ->toArray();

            $rootDecoratedDefinition = $this->definitions[$id];
            $decoratorsCount = count($decoratorDefinitions);
            for ($i = 0; $i < $decoratorsCount; $i++) {
                $previousDecorator = $decoratorDefinitions[$i - 1] ?? null;
                $currentDecorator = $decoratorDefinitions[$i];
                $nextDecorator = $decoratorDefinitions[$i + 1] ?? null;

                if ($i === 0) {
                    $rootDecoratedDefinition->setDecoratedBy($currentDecorator->getId());
                }

                $currentDecoratorArguments = $currentDecorator->getArguments();
                foreach ($currentDecoratorArguments as $index => $argument) {
                    if ($argument instanceof DecoratorReference && $argument->id === $id && $previousDecorator) {
                        $currentDecoratorArguments[$index] = new DecoratorReference(
                            $previousDecorator->getId(),
                            $argument->priority,
                            $argument->signature,
                        );
                    }
                }
                $currentDecorator->setArguments($currentDecoratorArguments);

                if ($previousDecorator) {
                    $currentDecorator
                        ->getDecorates()
                        ?->setId($previousDecorator->getId());
                }

                if ($nextDecorator) {
                    $currentDecorator->setDecoratedBy($nextDecorator->getId());
                }
            }
        }
    }
}
