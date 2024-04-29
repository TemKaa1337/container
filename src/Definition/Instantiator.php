<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Definition;

use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Model\ClassDefinition;
use Temkaa\SimpleContainer\Model\Definition\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Model\Definition\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Model\Definition\Reference;
use Temkaa\SimpleContainer\Model\Definition\ReferenceInterface;
use Temkaa\SimpleContainer\Model\DefinitionInterface;
use Temkaa\SimpleContainer\Model\InterfaceDefinition;
use Temkaa\SimpleContainer\Repository\DefinitionRepository;

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
                    id: $definition->getImplementedById()
                ),
            );
        }

        /** @var ClassDefinition $definition */
        if ($definition->isSingleton()) {
            return $definition->getInstance();
        }

        $arguments = [];
        foreach ($definition->getArguments() as $argument) {
            if ($argument instanceof ReferenceInterface) {
                $resolvedArgument = match (true) {
                    $argument instanceof Reference => $this->definitionRepository->find($argument->id),
                    $argument instanceof TaggedReference => $this->definitionRepository->findAllByTag($argument->tag),
                    $argument instanceof DecoratorReference => $this->instantiate($this->definitionRepository->find($argument->id)),
                };

                $arguments[] = $resolvedArgument instanceof ClassDefinition
                    ? $resolvedArgument->getInstance()
                    : array_map($this->instantiate(...), $resolvedArgument);
            } else {
                $arguments[] = $argument;
            }
        }

        $reflection = new ReflectionClass($definition->getId());

        return $reflection->getConstructor()
            ? $reflection->newInstanceArgs($arguments)
            : $reflection->newInstanceWithoutConstructor();
    }
}
