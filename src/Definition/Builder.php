<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Definition;

use LogicException;
use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
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
use Temkaa\SimpleContainer\Factory\Definition\InterfaceFactory;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Config\Decorator;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Definition\DefinitionInterface;
use Temkaa\SimpleContainer\Model\Definition\InterfaceDefinition;
use Temkaa\SimpleContainer\Model\Definition\UnboundInterfaceDefinition;
use Temkaa\SimpleContainer\Model\Reference\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Model\Reference\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Model\Reference\Reference;
use Temkaa\SimpleContainer\Util\ExpressionParser;
use Temkaa\SimpleContainer\Util\Extractor\AttributeExtractor;
use Temkaa\SimpleContainer\Util\Extractor\ClassExtractor;
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
     * @var Config[] $configs
     */
    private readonly array $configs;

    /**
     * @var DefinitionInterface[]
     */
    private array $definitions = [];

    /**
     * @var array<class-string, true>
     */
    private array $definitionsBuilding = [];

    /**
     * @var string[]
     */
    private array $excludedClasses;

    private ExpressionParser $expressionParser;

    private InterfaceFactory $interfaceFactory;

    private Config $resolvingConfig;

    /**
     * @var array<class-string, UnboundInterfaceDefinition>
     */
    private array $unboundInterfaces = [];

    /**
     * @param Config[] $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
        $this->expressionParser = new ExpressionParser();
        $this->interfaceFactory = new InterfaceFactory();
        $this->classExtractor = new ClassExtractor();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return DefinitionInterface[]
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function build(): array
    {
        foreach ($this->configs as $config) {
            $this->resolvingConfig = $config;

            /** @psalm-suppress NamedArgumentNotAllowed */
            $includedClasses = array_merge(
                ...array_map(
                fn (string $path): array => $this->classExtractor->extract(realpath($path)),
                $config->getIncludedPaths(),
            ),
            );

            /** @psalm-suppress NamedArgumentNotAllowed */
            $this->excludedClasses = array_merge(
                ...array_map(
                fn (string $path): array => $this->classExtractor->extract(realpath($path)),
                $config->getExcludedPaths(),
            ),
            );

            foreach ($includedClasses as $class) {
                $this->buildDefinition($class, failIfUninstantiable: false);
            }
        }

        $interfaceImplementations = [];
        foreach ($this->definitions as $definition) {
            if (!$definition instanceof ClassDefinition) {
                continue;
            }

            if (!$interfaces = $definition->getImplements()) {
                continue;
            }

            foreach ($interfaces as $interface) {
                $interfaceImplementations[$interface] ??= [];
                $interfaceImplementations[$interface][] = $definition->getId();
            }
        }

        if ($interfaceImplementations) {
            foreach ($interfaceImplementations as $interface => $definitionIds) {
                if (count($definitionIds) === 1) {
                    $this->definitions[$interface] = $this->interfaceFactory->create(
                        $interface,
                        implementedById: current($definitionIds),
                    );

                    continue;
                }

                $interfaceDecorators = array_values(
                    array_filter(
                        $definitionIds,
                        function (string $definitionId) use ($interface): bool {
                            /** @var ClassDefinition $definition */
                            $definition = $this->definitions[$definitionId];

                            return $definition->getDecorates()?->getId() === $interface;
                        },
                    ),
                );

                if (!$interfaceDecorators) {
                    continue;
                }

                $interfaceImplementations = array_values(
                    array_filter(
                        $definitionIds,
                        function (string $definitionId): bool {
                            /** @var ClassDefinition $definition */
                            $definition = $this->definitions[$definitionId];

                            return $definition->getDecorates() === null;
                        },
                    ),
                );

                if (count($interfaceImplementations) === 1) {
                    $this->definitions[$interface] = $this->interfaceFactory->create(
                        $interface,
                        implementedById: current($interfaceImplementations),
                    );
                }
            }
        }

        foreach ($this->unboundInterfaces as $definitionId => $unboundInterface) {
            if (!isset($this->definitions[$unboundInterface->getId()])) {
                throw new EntryNotFoundException(
                    sprintf(
                        'Could not find interface implementation for "%s".',
                        $unboundInterface->getId(),
                    ),
                );
            }

            /** @var ClassDefinition $definition */
            $definition = $this->definitions[$definitionId];

            $resolvedArguments = [];
            foreach ($definition->getArguments() as $argument) {
                if ($argument !== $unboundInterface) {
                    $resolvedArguments[] = $argument;

                    continue;
                }

                $resolvedArguments[] = new Reference($unboundInterface->getId());
            }

            if ($resolvedArguments === $definition->getArguments()) {
                throw new LogicException('Something went wrong.');
            }

            $definition->setArguments($resolvedArguments);
        }

        $this->updateDecoratorReferences();

        return $this->definitions;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param ReflectionParameter[] $arguments
     * @param ReflectionParameter   $argument
     * @param ClassDefinition       $definition
     * @param class-string          $id
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function buildArgument(
        array $arguments,
        ReflectionParameter $argument,
        ClassDefinition $definition,
        string $id,
    ): mixed {
        (new ArgumentValidator())->validate($argument, $id);

        if ($decorates = $definition->getDecorates()) {
            if (count($arguments) === 1 || $decorates->getSignature() === $argument->getName()) {
                return new DecoratorReference(
                    $decorates->getId(), $decorates->getPriority(), $decorates->getSignature(),
                );
            }

            $argumentNames = array_map(
                static fn (ReflectionParameter $argument): string => $argument->getName(),
                $arguments,
            );

            if (!in_array($decorates->getSignature(), $argumentNames, strict: true)) {
                throw new UnresolvableArgumentException(
                    sprintf(
                        'Could not resolve decorated class in class "%s" as it does not have argument named "%s".',
                        $definition->getId(),
                        $decorates->getSignature(),
                    ),
                );
            }
        }

        /** @var ReflectionNamedType $argumentType */
        $argumentType = $argument->getType();

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

            return TypeCaster::cast(
                $this->expressionParser->parse($expression),
                $argumentType->getName(),
            );
        }

        if ($argumentType->isBuiltin()) {
            $argumentName = $argument->getName();

            $boundClassInfo = $this->resolvingConfig->getBoundedClasses()[$id] ?? null;

            $classBoundVars = $boundClassInfo?->getBoundedVariables() ?? [];
            $globalBoundVars = $this->resolvingConfig->getBoundedVariables();

            $boundVariableValue = $classBoundVars[$argumentName] ?? $globalBoundVars[$argumentName] ?? null;
            $hasBoundVariable = (bool) $boundVariableValue;

            if ($hasBoundVariable && str_starts_with((string) $boundVariableValue, '!tagged')) {
                /** @psalm-suppress PossiblyNullArgument */
                $tag = trim(str_replace('!tagged', '', $boundVariableValue));

                $resolvedValue = new TaggedReference($tag);
            } else {
                /** @psalm-suppress PossiblyNullArgument */
                $resolvedValue = match (true) {
                    $hasBoundVariable                                 => TypeCaster::cast(
                        $this->expressionParser->parse($boundVariableValue),
                        $argumentType->getName(),
                    ),
                    $argumentType->allowsNull() && !$hasBoundVariable => null,
                    default                                           => throw new UnresolvableArgumentException(
                        sprintf(
                            'Cannot instantiate entry "%s" with argument "%s::%s".',
                            $id,
                            $argumentName,
                            $argumentType->getName(),
                        ),
                    ),
                };
            }

            return $resolvedValue;
        }

        $entryId = $argumentType->getName();

        $dependencyReflection = new ReflectionClass($entryId);
        if ($dependencyReflection->isInterface()) {
            $interfaceName = $dependencyReflection->getName();
            if (!$this->resolvingConfig->hasBoundInterface($interfaceName)) {
                // throw new EntryNotFoundException(
                //     sprintf(
                //         'Could not find interface implementation for "%s".',
                //         $interfaceName,
                //     ),
                // );
                return $this->unboundInterfaces[$definition->getId()] = new UnboundInterfaceDefinition($interfaceName);
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

        if ($boundClassInfo = $this->resolvingConfig->getBoundedClass($id)) {
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
            $definition->addArgument(
                $this->buildArgument($arguments, $argument, $definition, $id),
            );
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

        $boundClassInfo = $this->resolvingConfig->getBoundedClass($reflection->getName());

        $definition
            ->addTags($boundClassInfo?->getTags() ?? [])
            ->addTags(AttributeExtractor::extractParameters($classTags, 'name'))
            ->addAliases(
                array_values(
                    array_unique(
                        array_merge(
                            AttributeExtractor::extractParameters($classAliases, 'name'),
                            $this->resolvingConfig->getBoundedClass($definition->getId())?->getAliases() ?? [],
                        ),
                    ),
                ),
            );

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

        if ($decorates = $boundClassInfo?->getDecorates()) {
            $definition->setDecorates($decorates);
        } else if ($decoratesAttribute = $reflection->getAttributes(Decorates::class)) {
            $decoratesAttribute = current($decoratesAttribute)->newInstance();

            $definition->setDecorates(
                new Decorator(
                    $decoratesAttribute->id,
                    $decoratesAttribute->priority,
                    str_replace('$', '', $decoratesAttribute->signature),
                ),
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
            usort(
                $definitions,
                static function (ClassDefinition $prev, ClassDefinition $current): int {
                    /** @psalm-suppress PossiblyNullReference */
                    $prevPriority = $prev->getDecorates()->getPriority();
                    /** @psalm-suppress PossiblyNullReference */
                    $currentPriority = $current->getDecorates()->getPriority();

                    if ($prevPriority === $currentPriority) {
                        return 0;
                    }

                    return $prevPriority > $currentPriority ? -1 : 1;
                },
            );

            $rootDecoratedDefinition = $this->definitions[$id];
            $decoratorsCount = count($definitions);
            for ($i = 0; $i < $decoratorsCount; $i++) {
                $previousDecorator = $i === 0 ? null : $definitions[$i - 1];
                $currentDecorator = $definitions[$i];
                $nextDecorator = $definitions[$i + 1] ?? null;

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
                            str_replace('$', '', $decorates->getSignature()),
                        ),
                    );
                }

                if ($nextDecorator) {
                    $currentDecorator->setDecoratedBy($nextDecorator->getId());
                }
            }
        }
    }
}
