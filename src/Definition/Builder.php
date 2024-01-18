<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Temkaa\SimpleContainer\Attribute\Alias;
use Temkaa\SimpleContainer\Attribute\Bind\Parameter;
use Temkaa\SimpleContainer\Attribute\Bind\Tagged;
use Temkaa\SimpleContainer\Attribute\NonAutowirable;
use Temkaa\SimpleContainer\Attribute\Tag;
use Temkaa\SimpleContainer\Config;
use Temkaa\SimpleContainer\Definition\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\NonAutowirableClassException;
use Temkaa\SimpleContainer\Exception\UninstantiableEntryException;
use Temkaa\SimpleContainer\Exception\UnresolvableArgumentException;
use Temkaa\SimpleContainer\ExpressionParser;
use Temkaa\SimpleContainer\TypeCaster;
use Temkaa\SimpleContainer\Validator\ArgumentValidator;

final class Builder
{
    private readonly Config $config;

    /**
     * @var Definition[]
     */
    private array $definitions = [];

    /**
     * @var array<class-string, true>
     */
    private array $definitionsBuilding = [];

    private readonly ExpressionParser $expressionParser;

    private readonly TypeCaster $typeCaster;

    public function __construct(Config $config, array $env)
    {
        $this->config = $config;
        $this->expressionParser = new ExpressionParser($env);
        $this->typeCaster = new TypeCaster();
    }

    /**
     * @return Definition[]
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function build(): array
    {
        foreach ($this->config->getAutowiredClasses() as $class) {
            $this->buildDefinition($class, failIfUninstantiable: false);
        }

        return $this->definitions;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function buildArgument(ReflectionParameter $argument, string $id): mixed
    {
        // TODO: what arguments cannot be solved in preparation time? (what can be deferred?)
        // 1. tagged iterator (done)
        // 2. decorator

        (new ArgumentValidator())->validate($argument, $id);

        $argumentType = $argument->getType();
        if ($argumentAttributes = $argument->getAttributes(Tagged::class)) {
            $boundTagName = $this->extractAttributeParameters($argumentAttributes, parameter: 'tag')[0];

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
            $expression = $this->extractAttributeParameters($argumentAttributes, parameter: 'expression')[0];

            $parsedValue = $this->expressionParser->parse($expression);

            return $this->typeCaster->cast($parsedValue, $argumentType->getName());
        }

        if ($argumentType->isBuiltin()) {
            $argumentName = $argument->getName();

            $boundVars = $this->config->getClassBoundVariables($id);
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
                    : $this->typeCaster->cast($boundVars[$argumentName], $argumentType->getName());
            }

            return $resolvedValue;
        }

        $entryId = $argumentType->getName();

        $dependencyReflection = new ReflectionClass($entryId);
        if ($dependencyReflection->isInterface()) {
            $interfaceImplementation = $this->config->getInterfaceImplementation($dependencyReflection->getName());

            $this->buildDefinition($interfaceImplementation);

            return new Reference($interfaceImplementation);
        }

        if (!isset($this->definitions[$entryId])) {
            $this->buildDefinition($entryId);
        }

        return new Reference($entryId);
    }

    /**
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

        if (in_array($id, $this->config->getNonAutowiredClasses(), strict: true)) {
            throw new NonAutowirableClassException(
                sprintf('Cannot autowire class "%s" as it is in "exclude" config parameter.', $id),
            );
        }

        $definition = (new Definition())->setId($id);

        $nonAutowirableTags = $this->extractAttributes($reflection->getAttributes(), NonAutowirable::class);
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
     * @template T of ReflectionClass
     * @template C of object
     *
     * @param T<C>[] $attributes
     * @param string $parameter
     *
     * @return string[]
     *
     * @throws ReflectionException
     */
    private function extractAttributeParameters(array $attributes, string $parameter): array
    {
        return array_map(
            static fn (ReflectionAttribute $attribute): string => $attribute->newInstance()->{$parameter},
            $attributes,
        );
    }

    /**
     * @template T of ReflectionAttribute
     * @template C of object
     *
     * @param T[]             $attributes
     * @param class-string<C> $class
     *
     * @return T<C>[]
     */
    private function extractAttributes(array $attributes, string $class): array
    {
        return array_values(
            array_filter(
                $attributes,
                static fn (ReflectionAttribute $attribute): bool => $attribute->getName() === $class,
            ),
        );
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
     * @throws ReflectionException
     */
    private function populateDefinition(Definition $definition, ReflectionClass $r): void
    {
        $classAttributes = $r->getAttributes();

        $classTags = $this->extractAttributes($classAttributes, Tag::class);
        $classAliases = $this->extractAttributes($classAttributes, Alias::class);

        $definition
            ->addTags($this->config->getClassTags($r->getName()))
            ->addTags($this->extractAttributeParameters($classTags, 'name'))
            ->addAliases($this->extractAttributeParameters($classAliases, 'name'));

        $interfaces = $r->getInterfaces();
        $definition->addImplements(array_keys($interfaces));
        foreach ($interfaces as $interface) {
            $interfaceName = $interface->getName();
            if ($this->config->hasImplementation($interfaceName)
                && $this->config->getInterfaceImplementation($interfaceName) === $r->getName()
            ) {
                $definition->addAlias($interfaceName);
            }

            $interfaceTags = $this->extractAttributes($interface->getAttributes(), Tag::class);
            $definition->addTags($this->extractAttributeParameters($interfaceTags, 'name'));
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
