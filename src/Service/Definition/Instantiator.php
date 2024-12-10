<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Temkaa\Container\Enum\Attribute\Bind\IteratorFormat;
use Temkaa\Container\Model\Definition\ClassDefinition;
use Temkaa\Container\Model\Definition\DefinitionInterface;
use Temkaa\Container\Model\Definition\InterfaceDefinition;
use Temkaa\Container\Model\Reference\Deferred\DecoratorReference;
use Temkaa\Container\Model\Reference\Deferred\InstanceOfIteratorReference;
use Temkaa\Container\Model\Reference\Deferred\InterfaceReference;
use Temkaa\Container\Model\Reference\Deferred\TaggedIteratorReference;
use Temkaa\Container\Model\Reference\Reference;
use Temkaa\Container\Model\Reference\ReferenceInterface;
use Temkaa\Container\Repository\DefinitionRepository;
use Temkaa\Container\Service\CachingReflector;
use function array_map;
use function end;
use function explode;

/**
 * @internal
 */
final readonly class Instantiator
{
    public function __construct(
        private DefinitionRepository $definitionRepository,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function instantiate(DefinitionInterface $definition): object
    {
        if ($definition instanceof InterfaceDefinition) {
            return $this->instantiate(
                $this->definitionRepository->find(
                    id: $definition->getImplementedById(),
                ),
            );
        }

        /** @var ClassDefinition $definition */
        if ($definition->isSingleton()) {
            return $definition->getInstance();
        }

        $factory = $definition->getFactory();
        if ($factory) {
            if (!$factory->getMethod()->isStatic()) {
                /**
                 * @noinspection   PhpPossiblePolymorphicInvocationInspection
                 * @psalm-suppress UndefinedInterfaceMethod
                 *
                 * @var array $unresolvedArguments
                 */
                $unresolvedArguments = $this->definitionRepository->find($factory->getId())->getArguments();
                $factoryResolvedArguments = $this->resolveArguments($unresolvedArguments);

                $factoryReflection = CachingReflector::reflect($factory->getId());

                $factoryInstance = $factoryReflection->newInstanceArgs($factoryResolvedArguments);
            }

            $factoryMethodResolvedArguments = $this->resolveArguments($factory->getMethod()->getArguments());

            /**
             * @noinspection   PhpUndefinedVariableInspection
             * @psalm-suppress MixedMethodCall, PossiblyUndefinedVariable
             * @var object $instance
             */
            $instance = $factory->getMethod()->isStatic()
                ? $factory->getId()::{$factory->getMethod()->getName()}(...$factoryMethodResolvedArguments)
                : $factoryInstance->{$factory->getMethod()->getName()}(...$factoryMethodResolvedArguments);
        } else {
            $resolvedArguments = $this->resolveArguments($definition->getArguments());

            $reflection = CachingReflector::reflect($definition->getId());

            $instance = $reflection->newInstanceArgs($resolvedArguments);
        }

        foreach ($definition->getRequiredMethodCalls() as $method => $arguments) {
            /** @psalm-suppress MixedMethodCall */
            $instance->{$method}(...$this->resolveArguments($arguments));
        }

        return $instance;
    }

    /**
     * @throws ReflectionException
     */
    private function resolveArguments(array $arguments): array
    {
        $resolvedArguments = [];
        foreach ($arguments as $argument) {
            if (!$argument instanceof ReferenceInterface) {
                /** @psalm-suppress MixedAssignment */
                $resolvedArguments[] = $argument;

                continue;
            }

            if ($argument instanceof TaggedIteratorReference || $argument instanceof InstanceOfIteratorReference) {
                $resolvedArguments[] = $this->resolveIteratorArgument($argument);

                continue;
            }

            /** @var DecoratorReference|InterfaceReference|Reference $argument */
            $resolvedArguments[] = $this->instantiate($this->definitionRepository->find($argument->getId()));
        }

        return $resolvedArguments;
    }

    /**
     * @return array<string, object>|list<object>
     * @throws ReflectionException
     */
    private function resolveIteratorArgument(InstanceOfIteratorReference|TaggedIteratorReference $argument): array
    {
        /** @var ClassDefinition[] $objects */
        $objects = $argument instanceof InstanceOfIteratorReference
            ? $this->definitionRepository->findAllByInstanceOf($argument->getId(), $argument->getExclude())
            : $this->definitionRepository->findAllByTag($argument->getTag(), $argument->getExclude());

        $format = $argument->getFormat();
        if ($format === IteratorFormat::List) {
            return array_map($this->instantiate(...), $objects);
        }

        $result = [];
        if ($format === IteratorFormat::ArrayWithClassNamespaceKey) {
            foreach ($objects as $object) {
                $result[$object->getId()] = $this->instantiate($object);
            }

            return $result;
        }

        if ($format === IteratorFormat::ArrayWithClassNameKey) {
            foreach ($objects as $object) {
                $classNamespace = explode('\\', $object->getId());

                $result[end($classNamespace)] = $this->instantiate($object);
            }

            return $result;
        }

        $mapping = $argument->getCustomFormatMapping();
        foreach ($objects as $object) {
            $key = $mapping[$object->getId()];

            $result[$key] = $this->instantiate($object);
        }

        return $result;
    }
}
