<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Definition;

use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Model\Definition;
use Temkaa\SimpleContainer\Model\Definition\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Model\Definition\Reference;
use Temkaa\SimpleContainer\Model\Definition\ReferenceInterface;
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
    public function instantiate(Definition $definition): object
    {
        $instance = $definition->getInstance();
        if ($definition->isSingleton()) {
            return $instance;
        }

        $arguments = [];
        foreach ($definition->getArguments() as $argument) {
            if ($argument instanceof ReferenceInterface) {
                $definition = match (true) {
                    $argument instanceof Reference       => $this->definitionRepository->find($argument->id),
                    $argument instanceof TaggedReference => $this->definitionRepository->findByTag($argument->tag),
                };

                $arguments[] = $definition->getInstance();
            } else {
                $arguments[] = $argument;
            }
        }

        $reflection = new ReflectionClass($instance);
        return $reflection->getConstructor()
            ? $reflection->newInstanceArgs($arguments)
            : $reflection->newInstanceWithoutConstructor();
    }
}
