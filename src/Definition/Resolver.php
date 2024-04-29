<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Definition;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use Temkaa\SimpleContainer\Exception\CircularReferenceException;
use Temkaa\SimpleContainer\Model\Definition;
use Temkaa\SimpleContainer\Model\Definition\Deferred\DecoratorReference;
use Temkaa\SimpleContainer\Model\Definition\Reference;
use Temkaa\SimpleContainer\Model\Definition\ReferenceInterface;
use Temkaa\SimpleContainer\Repository\DefinitionRepository;

final class Resolver
{
    /**
     * @var array<class-string, true>
     */
    private array $definitionsResolving = [];

    /**
     * @param Definition[] $definitions
     */
    public function __construct(
        private readonly array $definitions,
    ) {
    }

    /**
     * @return Definition[]
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function resolve(): array
    {
        foreach ($this->definitions as $definition) {
            $this->resolveDefinition($definition);
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
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function resolveArgument(mixed $argument): mixed
    {
        if (!$argument instanceof ReferenceInterface) {
            return $argument;
        }

        if ($argument instanceof Reference || $argument instanceof DecoratorReference) {
            $this->resolveDefinition($this->definitions[$argument->id]);

            $instantiator = new Instantiator(new DefinitionRepository(array_values($this->definitions)));

            return $instantiator->instantiate($this->definitions[$argument->id]);
        }

        $definitionRepository = new DefinitionRepository(array_values($this->definitions));
        /** @psalm-suppress NoInterfaceProperties */
        $taggedDefinitions = $definitionRepository->findAllByTag($argument->tag);

        $resolvedArguments = [];
        foreach ($taggedDefinitions as $taggedDefinition) {
            $this->resolveDefinition($this->definitions[$taggedDefinition->getId()]);

            $instantiator = new Instantiator(new DefinitionRepository(array_values($this->definitions)));
            $resolvedArguments[] = $instantiator->instantiate($this->definitions[$taggedDefinition->getId()]);
        }

        return $resolvedArguments;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function resolveDefinition(Definition $definition): void
    {
        if ($definition->hasInstance()) {
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

        $reflection = new ReflectionClass($definition->getId());
        $instance = $resolvedArguments ? $reflection->newInstanceArgs($resolvedArguments) : $reflection->newInstance();
        $definition->setInstance($instance);

        $this->setResolving($definition->getId(), isResolving: false);
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
