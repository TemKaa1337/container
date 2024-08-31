<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Definition\DefinitionInterface;
use Temkaa\SimpleContainer\Model\Definition\InterfaceDefinition;
use Temkaa\SimpleContainer\Model\Reference\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Model\Reference\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Model\Reference\Reference;
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
        if (!$factory || !$factory->getMethod()->isStatic()) {
            $resolvedArguments = $this->resolveArguments($definition->getArguments());

            $reflection = new ReflectionClass($definition->getId());

            $instance = $reflection->newInstanceArgs($resolvedArguments);

            if (!$factory) {
                return $instance;
            }

            $factoryResolvedArguments = $this->resolveArguments($factory->getMethod()->getArguments());

            return $instance->{$factory->getMethod()->getName()}(...$factoryResolvedArguments);
        }

        $factoryResolvedArguments = $this->resolveArguments($factory->getMethod()->getArguments());

        return $factory->getId()::{$factory->getMethod()->getName()}(...$factoryResolvedArguments);
    }

    private function resolveArguments(array $arguments): array
    {
        $resolvedArguments = [];
        foreach ($arguments as $argument) {
            if (!$argument instanceof ReferenceInterface) {
                /** @psalm-suppress MixedAssignment */
                $resolvedArguments[] = $argument;

                continue;
            }

            /** @var DecoratorReference|Reference|TaggedReference $argument */
            $resolvedArgument = $argument instanceof TaggedReference
                ? array_map($this->instantiate(...), $this->definitionRepository->findAllByTag($argument->getTag()))
                : $this->instantiate($this->definitionRepository->find($argument->getId()));

            $resolvedArguments[] = $resolvedArgument;
        }

        return $resolvedArguments;
    }
}
