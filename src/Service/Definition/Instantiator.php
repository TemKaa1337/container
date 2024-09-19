<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition;

use ReflectionClass;
use ReflectionException;
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

                $factoryReflection = new ReflectionClass($factory->getId());

                $factoryInstance = $factoryReflection->newInstanceArgs($factoryResolvedArguments);
            }

            $factoryMethodResolvedArguments = $this->resolveArguments($factory->getMethod()->getArguments());

            /**
             * @psalm-suppress MixedMethodCall, PossiblyUndefinedVariable
             * @var object $instance
             */
            $instance = $factory->getMethod()->isStatic()
                ? $factory->getId()::{$factory->getMethod()->getName()}(...$factoryMethodResolvedArguments)
                : $factoryInstance->{$factory->getMethod()->getName()}(...$factoryMethodResolvedArguments);
        } else {
            $resolvedArguments = $this->resolveArguments($definition->getArguments());

            $reflection = new ReflectionClass($definition->getId());

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

            /**
             * @var TaggedIteratorReference|InstanceOfIteratorReference|DecoratorReference|Reference|InterfaceReference $argument
             */
            $resolvedArgument = match (true) {
                $argument instanceof TaggedIteratorReference     => array_map(
                    $this->instantiate(...),
                    $this->definitionRepository->findAllByTag($argument->getTag()),
                ),
                $argument instanceof InstanceOfIteratorReference => array_map(
                    $this->instantiate(...),
                    $this->definitionRepository->findAllByInstanceOf($argument->getId()),
                ),
                default                                          => $this->instantiate(
                    $this->definitionRepository->find($argument->getId()),
                ),
            };

            $resolvedArguments[] = $resolvedArgument;
        }

        return $resolvedArguments;
    }
}
