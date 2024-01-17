<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Definition\Deferred\TaggedReference;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;

final class Resolver
{
    /**
     * @var array<class-string, true>
     */
    private array $definitionsResolving = [];

    public function __construct(
        /**
         * @var Definition[]
         */
        private readonly array $definitions,
    ) {
    }

    /**
     * @return Definition[]
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function resolveAll(): array
    {
        foreach ($this->definitions as $definition) {
            $this->resolve($definition);
        }

        return $this->definitions;
    }

    /**
     * @param class-string $id
     */
    private function isDefinitionResolving(string $id): bool
    {
        return $this->definitionsResolving[$id] ?? false;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function resolve(Definition $definition): void
    {
        if ($definition->getInstance()) {
            return;
        }

        if ($this->isDefinitionResolving($definition->getId())) {
            throw new CircularReferenceException($definition->getId(), array_keys($this->definitionsResolving));
        }

        $this->setResolving($definition->getId(), isResolving: true);

        $resolvedArguments = [];
        foreach ($definition->getArguments() as $argument) {
            $resolvedArguments[] = $this->resolveArgument($argument);
        }

        $r = new ReflectionClass($definition->getId());
        $instance = $resolvedArguments ? $r->newInstanceArgs($resolvedArguments) : $r->newInstance();
        $definition->setInstance($instance);

        $this->setResolving($definition->getId(), isResolving: false);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function resolveArgument(mixed $argument): mixed
    {
        if ($argument instanceof Reference) {
            $this->resolve($this->definitions[$argument->id]);

            return $this->definitions[$argument->id]->getInstance();
        }

        if ($argument instanceof TaggedReference) {
            /** @var Definition[] $taggedDefinitions */
            $taggedDefinitions = array_values(
                array_filter(
                    array_values($this->definitions),
                    static fn (Definition $definition): bool => in_array(
                        $argument->tag,
                        $definition->getTags(),
                        strict: true,
                    ),
                ),
            );

            $resolvedArgument = [];
            foreach ($taggedDefinitions as $taggedDefinition) {
                $this->resolve($this->definitions[$taggedDefinition->getId()]);

                $resolvedArgument[] = $this->definitions[$taggedDefinition->getId()]->getInstance();
            }

            return $resolvedArgument;
        }

        return $argument;
    }

    /**
     * @param class-string $id
     */
    private function setResolving(string $id, bool $isResolving): void
    {
        if ($isResolving) {
            $this->definitionsResolving[$id] = true;
        } else {
            unset($this->definitionsResolving[$id]);
        }
    }
}
