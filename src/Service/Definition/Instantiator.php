<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Definition\DefinitionInterface;
use Temkaa\SimpleContainer\Model\Definition\InterfaceDefinition;
use Temkaa\SimpleContainer\Model\Reference\Deferred\InstanceOfIteratorReference;
use Temkaa\SimpleContainer\Model\Reference\Deferred\TaggedIteratorReference;
use Temkaa\SimpleContainer\Model\Reference\ReferenceInterface;
use Temkaa\SimpleContainer\Repository\DefinitionRepository;

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

            $resolvedArgument = match (true) {
                $argument instanceof TaggedIteratorReference => array_map(
                    $this->instantiate(...),
                    $this->definitionRepository->findAllByTag($argument->getTag()),
                ),
                $argument instanceof InstanceOfIteratorReference => array_map(
                    $this->instantiate(...),
                    $this->definitionRepository->findAllByInstanceOf($argument->getId()),
                ),
                default => $this->instantiate(
                    $this->definitionRepository->find($argument->getId()),
                ),
            };

            $resolvedArguments[] = $resolvedArgument;
        }

        return $resolvedArguments;
    }
}
