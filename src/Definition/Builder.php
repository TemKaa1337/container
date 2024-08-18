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
use Temkaa\SimpleCollections\Enum\SortOrder;
use Temkaa\SimpleCollections\Model\Sort\ByField;
use Temkaa\SimpleContainer\Attribute\Alias;
use Temkaa\SimpleContainer\Attribute\Autowire;
use Temkaa\SimpleContainer\Attribute\Bind\Parameter;
use Temkaa\SimpleContainer\Attribute\Bind\Tagged;
use Temkaa\SimpleContainer\Attribute\Decorates;
use Temkaa\SimpleContainer\Attribute\Tag;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\EntryNotFoundException;
use Temkaa\SimpleContainer\Exception\NonAutowirableClassException;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Factory\Definition\DecoratorFactory;
use Temkaa\SimpleContainer\Factory\Definition\InterfaceFactory;
use Temkaa\SimpleContainer\Model\ClassDefinition;
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Temkaa\SimpleContainer\Model\Container\ConfigNew;
use Temkaa\SimpleContainer\Model\Definition\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Model\Definition\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Model\Definition\Reference;
use Temkaa\SimpleContainer\Model\DefinitionInterface;
use Temkaa\SimpleContainer\Model\InterfaceDefinition;
use Temkaa\SimpleContainer\Util\AttributeExtractor;
use Temkaa\SimpleContainer\Util\ClassExtractor;
use Temkaa\SimpleContainer\Util\ExpressionParser;
use Temkaa\SimpleContainer\Util\TypeCaster;
use Temkaa\SimpleContainer\Validator\ArgumentValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @internal
 */
final class Builder
{
    private ClassExtractor $classExtractor;

    /**
     * @var ConfigNew[] $configs
     */
    private readonly array $configs;

    private DecoratorFactory $decoratorFactory;

    /**
     * @var DefinitionInterface[]
     */
    private array $definitions = [];

    /**
     * @var array<class-string, true>
     */
    private array $definitionsBuilding = [];

    private array $excludedClasses;

    private ExpressionParser $expressionParser;

    private array $includedClasses;

    private InterfaceFactory $interfaceFactory;

    private ConfigNew $resolvingConfig;

    /**
     * @param ConfigNew[] $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
        $this->decoratorFactory = new DecoratorFactory();
        $this->expressionParser = new ExpressionParser();
        $this->interfaceFactory = new InterfaceFactory();
        $this->classExtractor = new ClassExtractor();
    }

    /**
     * @return DefinitionInterface[]
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function build(): array
    {
        foreach ($this->configs as $config) {
            $this->resolvingConfig = $config;

            $this->includedClasses = array_merge(
                ...array_map(
                fn (string $path): array => $this->classExtractor->extract(realpath($path)),
                $config->getIncludedPaths(),
            ),
            );

            $this->excludedClasses = array_merge(
                ...array_map(
                fn (string $path): array => $this->classExtractor->extract(realpath($path)),
                $config->getExcludedPaths(),
            ),
            );

            foreach ($this->includedClasses as $class) {
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
    private function buildArgument(ReflectionParameter $argument, ClassDefinition $definition, string $id): mixed
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

            $boundClassInfo = $this->resolvingConfig->getBoundedClasses()[$id] ?? null;

            $classBoundVars = $boundClassInfo?->getBoundVariables() ?? [];
            $globalBoundVars = $this->resolvingConfig->getBoundedVariables();

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
                    : TypeCaster::cast($this->expressionParser->parse($boundVariableValue), $argumentType->getName());
            }

            return $resolvedValue;
        }

        /** @var class-string $entryId */
        $entryId = $argumentType->getName();

        $dependencyReflection = new ReflectionClass($entryId);
        if ($dependencyReflection->isInterface()) {
            $interfaceName = $dependencyReflection->getName();
            if (!$this->resolvingConfig->hasBoundInterface($interfaceName)) {
                throw new EntryNotFoundException(
                    sprintf(
                        'Could not find interface implementation for "%s".',
                        $interfaceName,
                    ),
                );
            }

            $interfaceImplementation = $this->resolvingConfig->getBoundInterfaceImplementation($interfaceName);

            $this->definitions[$interfaceName] = $this->interfaceFactory->create(
                $interfaceName,
                $interfaceImplementation,
            );

            $this->buildDefinition($interfaceImplementation);

            return new Reference($interfaceName);
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

        if (in_array($id, $this->excludedClasses, strict: true)) {
            throw new NonAutowirableClassException(
                sprintf('Cannot autowire class "%s" as it is in "exclude" config parameter.', $id),
            );
        }

        $definition = (new ClassDefinition())->setId($id);

        $boundClassInfo = $this->resolvingConfig->getBoundedClasses()[$id] ?? null;

        if ($boundClassInfo) {
            $definition->setIsSingleton($boundClassInfo->isSingleton());
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
    private function populateDefinition(ClassDefinition $definition, ReflectionClass $reflection): void
    {
        $classTags = $reflection->getAttributes(Tag::class);
        $classAliases = $reflection->getAttributes(Alias::class);

        $boundClassInfo = $this->resolvingConfig->getBoundedClasses()[$reflection->getName()] ?? null;

        $definition
            ->addTags($boundClassInfo?->getTags() ?? [])
            ->addTags(AttributeExtractor::extractParameters($classTags, 'name'))
            ->addAliases(AttributeExtractor::extractParameters($classAliases, 'name'));

        $interfaces = $reflection->getInterfaces();
        $definition->addImplements(array_keys($interfaces));
        foreach ($interfaces as $interface) {
            $interfaceName = $interface->getName();

            if (
                $this->resolvingConfig->hasBoundInterface($interfaceName)
                && $this->resolvingConfig->getBoundInterfaceImplementation($interfaceName) === $reflection->getName()
            ) {
                $this->definitions[$interfaceName] = $this->interfaceFactory->create(
                    $interfaceName,
                    implementedById: $reflection->getName(),
                );
            }

            $interfaceTags = $interface->getAttributes(Tag::class);
            $definition->addTags(AttributeExtractor::extractParameters($interfaceTags, 'name'));
        }

        $decoratesAttribute = $reflection->getAttributes(Decorates::class);
        $boundClassInfo = $this->resolvingConfig->getBoundedClasses()[$definition->getId()] ?? null;
        $decoratesConfig = $boundClassInfo?->getDecorates();
        if ($decoratesAttribute || $decoratesConfig) {
            $decoratesAttribute = $decoratesAttribute ?current($decoratesAttribute)->newInstance() : null;
            $definition->setDecorates(
                $decoratesAttribute
                    ? new Decorator($decoratesAttribute->id, $decoratesAttribute->priority, $decoratesAttribute->signature)
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
        /** @var array<class-string, ClassDefinition[]> $decorators */
        $decorators = [];

        $definitions = array_filter(
            $this->definitions,
            static fn (DefinitionInterface $definition): bool => $definition instanceof ClassDefinition,
        );

        foreach ($definitions as $definition) {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            if ($decorates = $definition->getDecorates()) {
                $decorators[$decorates->getId()] ??= [];
                $decorators[$decorates->getId()][] = $definition;
            }
        }

        foreach ($decorators as $id => $definitions) {
            /** @var ClassDefinition[] $decoratorDefinitions */
            $decoratorDefinitions = (new Collection($definitions))
                ->sort(new ByField(field: 'decorates.priority', order: SortOrder::Desc))
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
                    if ($argument instanceof DecoratorReference) {
                        if ($argument->id === $id && $previousDecorator) {
                            $currentDecoratorArguments[$index] = new DecoratorReference(
                                $i === 0 && $rootDecoratedDefinition instanceof InterfaceDefinition
                                    ? $this->definitions[$rootDecoratedDefinition->getImplementedById()]->getId()
                                    : $previousDecorator->getId(),
                                $argument->priority,
                                $argument->signature,
                            );
                        } else if ($i === 0 && $rootDecoratedDefinition instanceof InterfaceDefinition) {
                            $currentDecoratorArguments[$index] = new DecoratorReference(
                                $this->definitions[$rootDecoratedDefinition->getImplementedById()]->getId(),
                                $argument->priority,
                                $argument->signature,
                            );
                        }
                    }
                }

                $currentDecorator->setArguments($currentDecoratorArguments);

                if ($previousDecorator && $decorates = $currentDecorator->getDecorates()) {
                    $currentDecorator->setDecorates(
                        new Decorator(
                            $previousDecorator->getId(),
                            $decorates->getPriority(),
                            $decorates->getSignature(),
                        )
                    );
                }

                if ($nextDecorator) {
                    $currentDecorator->setDecoratedBy($nextDecorator->getId());
                }
            }
        }
    }
}
