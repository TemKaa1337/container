<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Definition;

use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Definition\DefinitionInterface;
use Temkaa\SimpleContainer\Model\Definition\InterfaceDefinition;
use Temkaa\SimpleContainer\Model\Reference\Deferred\DecoratorReference;
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

        $arguments = [];
        foreach ($definition->getArguments() as $argument) {
            if (!$argument instanceof ReferenceInterface) {
                $arguments[] = $argument;

                continue;
            }

            $resolvedArgument = $argument instanceof Reference || $argument instanceof DecoratorReference
                ? $this->instantiate($this->definitionRepository->find($argument->getId()))
                : array_map($this->instantiate(...), $this->definitionRepository->findAllByTag($argument->getId()));

            $arguments[] = $resolvedArgument;
        }

        $reflection = new ReflectionClass($definition->getId());

        return $reflection->newInstanceArgs($arguments);
    }
}
