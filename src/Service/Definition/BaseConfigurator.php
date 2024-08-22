<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

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
use Temkaa\SimpleContainer\Exception\NonAutowirableClassException;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Factory\Definition\DecoratorFactory;
use Temkaa\SimpleContainer\Factory\Definition\InterfaceFactory;
use Temkaa\SimpleContainer\Model\Config;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Reference\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Model\Reference\Deferred\InterfaceReference;
use Temkaa\SimpleContainer\Model\Reference\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Model\Reference\Reference;
use Temkaa\SimpleContainer\Util\ExpressionParser;
use Temkaa\SimpleContainer\Util\Extractor\AttributeExtractor;
use Temkaa\SimpleContainer\Util\Extractor\ClassExtractor;
use Temkaa\SimpleContainer\Util\Flag;
use Temkaa\SimpleContainer\Util\TypeCaster;
use Temkaa\SimpleContainer\Validator\Argument\DecoratorValidator;
use Temkaa\SimpleContainer\Validator\ArgumentValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @internal
 */
final class BaseConfigurator implements ConfiguratorInterface
{
    private ClassExtractor $classExtractor;

    /**
     * @var Config[] $configs
     */
    private readonly array $configs;

    private Bag $definitions;

    /**
     * @var string[]
     */
    private array $excludedClasses;

    private ExpressionParser $expressionParser;

    private Config $resolvingConfig;

    /**
     * @param Config[] $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
        $this->expressionParser = new ExpressionParser();
        $this->classExtractor = new ClassExtractor();
        $this->definitions = new Bag();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function configure(): Bag
    {
        foreach ($this->configs as $config) {
            $this->resolvingConfig = $config;

            $includedClasses = $this->classExtractor->extract(
                paths: array_map(realpath(...), $config->getIncludedPaths()),
            );

            $this->excludedClasses = $this->classExtractor->extract(
                paths: array_map(realpath(...), $config->getExcludedPaths()),
            );

            foreach ($includedClasses as $class) {
                $this->buildDefinition($class, failIfUninstantiable: false);
            }
        }

        return $this->definitions;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param ReflectionParameter $argument
     * @param ClassDefinition     $definition
     * @param class-string        $id
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function buildArgument(
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

            $boundClassInfo = $this->resolvingConfig->getBoundedClass($id);

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
                return new InterfaceReference($interfaceName);
            }

            $interfaceImplementation = $this->resolvingConfig->getBoundInterfaceImplementation($interfaceName);

            $this->definitions->add(
                InterfaceFactory::create(
                    $interfaceName,
                    $interfaceImplementation,
                ),
            );

            $this->buildDefinition($interfaceImplementation);

            return new Reference($interfaceName);
        }

        if (!$this->definitions->has($entryId)) {
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
        if ($this->definitions->has($id)) {
            return;
        }

        if (Flag::isToggled($id, group: 'definition')) {
            throw new CircularReferenceException($id, Flag::getToggled(group: 'definition'));
        }

        Flag::toggle($id, group: 'definition');

        try {
            $reflection = new ReflectionClass($id);
        } catch (ReflectionException) {
            throw new ClassNotFoundException($id);
        }

        if ($reflection->isInternal()) {
            throw new UninstantiableEntryException(sprintf('Cannot resolve internal entry "%s".', $id));
        }

        if (!$reflection->isInstantiable()) {
            Flag::untoggle($id, group: 'definition');

            if (!$failIfUninstantiable) {
                return;
            }

            throw new UninstantiableEntryException(sprintf('Cannot instantiate entry with id "%s".', $id));
        }

        if (in_array($id, $this->excludedClasses, strict: true)) {
            Flag::untoggle($id, group: 'definition');

            throw new NonAutowirableClassException(
                sprintf('Cannot autowire class "%s" as it is in "exclude" config parameter.', $id),
            );
        }

        $definition = (new ClassDefinition())->setId($id);

        if ($autowireTags = $reflection->getAttributes(Autowire::class)) {
            $isSingleton = AttributeExtractor::extract($autowireTags, index: 0)->singleton;
            $definition->setIsSingleton($isSingleton);
        }

        $isNonAutowirable = AttributeExtractor::hasParameterByValue($autowireTags, parameter: 'load', value: false);
        if ($isNonAutowirable) {
            Flag::untoggle($id, group: 'definition');

            if (!$failIfUninstantiable) {
                return;
            }

            throw new NonAutowirableClassException(
                sprintf('Class "%s" has NonAutowirable attribute and cannot be autowired.', $id),
            );
        }

        if ($boundClassInfo = $this->resolvingConfig->getBoundedClass($id)) {
            $definition->setIsSingleton($boundClassInfo->isSingleton());
        }

        $this->populateDefinition($definition, $reflection);

        if (!$constructor = $reflection->getConstructor()) {
            Flag::untoggle($id, group: 'definition');

            $this->definitions->add($definition);

            return;
        }

        $arguments = $constructor->getParameters();
        $decorates = $definition->getDecorates();

        (new DecoratorValidator())->validate($decorates, $arguments, $definition->getId());

        if ($decorates && count($arguments) === 1) {
            $definition->addArgument(
                new DecoratorReference(
                    $decorates->getId(),
                    $decorates->getPriority(),
                    $decorates->getSignature(),
                ),
            );
        } else {
            foreach ($arguments as $argument) {
                $definition->addArgument(
                    $this->buildArgument($argument, $definition, $id),
                );
            }
        }

        $this->definitions->add($definition);

        Flag::untoggle($id, group: 'definition');
    }

    private function populateDefinition(ClassDefinition $definition, ReflectionClass $reflection): void
    {
        $classTags = $reflection->getAttributes(Tag::class);
        $classAliases = $reflection->getAttributes(Alias::class);

        $boundClassInfo = $this->resolvingConfig->getBoundedClass($reflection->getName());

        $aliases = array_values(
            array_unique(
                array_merge(
                    AttributeExtractor::extractParameters($classAliases, parameter: 'name'),
                    $this->resolvingConfig->getBoundedClass($definition->getId())?->getAliases() ?? [],
                ),
            ),
        );

        $definition
            ->addTags($boundClassInfo?->getTags() ?? [])
            ->addTags(AttributeExtractor::extractParameters($classTags, parameter: 'name'))
            ->addAliases($aliases);

        $interfaces = $reflection->getInterfaces();
        $definition->addImplements(array_keys($interfaces));
        foreach ($interfaces as $interface) {
            $interfaceName = $interface->getName();

            if (
                $this->resolvingConfig->hasBoundInterface($interfaceName)
                && $this->resolvingConfig->getBoundInterfaceImplementation($interfaceName) === $reflection->getName()
            ) {
                $this->definitions->add(
                    InterfaceFactory::create(
                        $interfaceName,
                        implementedById: $reflection->getName(),
                    ),
                );
            }

            $interfaceTags = $interface->getAttributes(Tag::class);
            $definition->addTags(AttributeExtractor::extractParameters($interfaceTags, parameter: 'name'));
        }

        if ($decorates = $boundClassInfo?->getDecorates()) {
            $definition->setDecorates($decorates);
        } else if ($decoratesAttribute = $reflection->getAttributes(Decorates::class)) {
            $decoratesAttribute = AttributeExtractor::extract($decoratesAttribute, index: 0);

            $definition->setDecorates(DecoratorFactory::createFromAttribute($decoratesAttribute));
        }
    }
}
