<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Model\Definition;

final class Bag
{
    /**
     * @var array<class-string, DefinitionInterface>
     */
    private array $definitions = [];

    public function add(DefinitionInterface $definition): self
    {
        $this->definitions[$definition->getId()] = $definition;

        return $this;
    }

    /**
     * @return array<class-string, DefinitionInterface>
     */
    public function all(): array
    {
        return $this->definitions;
    }

    /**
     * @param class-string $id
     */
    public function get(string $id): DefinitionInterface
    {
        return $this->definitions[$id];
    }

    /**
     * @param class-string $id
     */
    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }
}
