<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use Temkaa\SimpleContainer\Attribute\Alias;
use Temkaa\SimpleContainer\Attribute\Bind\Parameter;
use Temkaa\SimpleContainer\Attribute\Bind\Tagged;
use Temkaa\SimpleContainer\Attribute\NonAutowirable;
use Temkaa\SimpleContainer\Attribute\Tag;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\NonAutowirableClassException;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\Model\Container\Config;
use Temkaa\SimpleContainer\Model\Definition;
use Temkaa\SimpleContainer\Model\Definition\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Model\Definition\Reference;
use Temkaa\SimpleContainer\Util\AttributeExtractor;
use Temkaa\SimpleContainer\Util\ExpressionParser;
use Temkaa\SimpleContainer\Util\TypeCaster;
use Temkaa\SimpleContainer\Validator\ArgumentValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class Builder
{
    /**
     * @var Config[] $configs
     */
    private readonly array $configs;

    /**
     * @var Definition[]
     */
    private array $definitions = [];

    /**
     * @var array<class-string, true>
     */
    private array $definitionsBuilding = [];

    private Config $resolvingConfig;

    public function __construct(array $configs)
    {
        $this->configs = $configs;
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

        return $this->definitions;
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
    private function buildArgument(ReflectionParameter $argument, string $id): mixed
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

            $parsedValue = ExpressionParser::parse($expression);

            return TypeCaster::cast($parsedValue, $argumentType->getName());
        }

        if ($argumentType->isBuiltin()) {
            $argumentName = $argument->getName();

            $boundVars = $this->resolvingConfig->getClassBoundVariables($id);
            if (isset($boundVars[$argumentName]) && str_starts_with($boundVars[$argumentName], '!tagged')) {
                $tag = trim(str_replace('!tagged', '', $boundVars[$argumentName]));

                $resolvedValue = new TaggedReference($tag);
            } else {
                if (!isset($boundVars[$argumentName]) && !$argumentType->allowsNull()) {
                    throw new UnresolvableArgumentException(
                        sprintf(
                            'Cannot instantiate entry "%s" with argument "%s::%s".',
                            $id,
                            $argumentName,
                            $argumentType->getName(),
                        ),
                    );
                }

                $resolvedValue = $argumentType->allowsNull() && !isset($boundVars[$argumentName])
                    ? null
                    : TypeCaster::cast($boundVars[$argumentName], $argumentType->getName());
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

        $nonAutowirableTags = $reflection->getAttributes(NonAutowirable::class);
        if ($nonAutowirableTags) {
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
            $definition->addArgument($this->buildArgument($argument, $id));
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
    private function populateDefinition(Definition $definition, ReflectionClass $r): void
    {
        $classTags = $r->getAttributes(Tag::class);
        $classAliases = $r->getAttributes(Alias::class);

        $definition
            ->addTags($this->resolvingConfig->getClassTags($r->getName()))
            ->addTags(AttributeExtractor::extractParameters($classTags, 'name'))
            ->addAliases(AttributeExtractor::extractParameters($classAliases, 'name'));

        $interfaces = $r->getInterfaces();
        $definition->addImplements(array_keys($interfaces));
        foreach ($interfaces as $interface) {
            $interfaceName = $interface->getName();

            if ($this->resolvingConfig->hasImplementation($interfaceName)
                && $this->resolvingConfig->getInterfaceImplementation($interfaceName) === $r->getName()
            ) {
                $definition->addAlias($interfaceName);
            }

            $interfaceTags = $interface->getAttributes(Tag::class);
            $definition->addTags(AttributeExtractor::extractParameters($interfaceTags, 'name'));
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
}
