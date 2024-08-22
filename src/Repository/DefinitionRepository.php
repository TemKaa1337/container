<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Repository;

use Temkaa\SimpleContainer\Attribute\Autowire;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;
use Temkaa\SimpleContainer\Model\Definition\DefinitionInterface;
use Temkaa\SimpleContainer\Model\Definition\InterfaceDefinition;

/**
 * @internal
 */
#[Autowire(load: false)]
final readonly class DefinitionRepository
{
    /**
     * @var array<class-string, DefinitionInterface>
     */
    private array $definitions;

    /**
     * @param DefinitionInterface[] $definitions
     */
    public function __construct(array $definitions)
    {
        $this->definitions = array_combine(
            array_map(
                static fn (DefinitionInterface $definition): string => $definition->getId(),
                $definitions,
            ),
            $definitions,
        );
    }

    public function find(string $id): DefinitionInterface
    {
        $entry = $this->definitions[$id] ?? $this->findOneByAlias($id);
        if (!$entry) {
            throw new EntryNotFoundException(sprintf('Could not find entry "%s".', $id));
        }

        return $this->resolveDecorators($entry);
    }

    /**
     * @return DefinitionInterface[]
     */
    public function findAllByTag(string $tag): array
    {
        $taggedDefinitions = [];
        foreach ($this->definitions as $definition) {
            if ($definition instanceof InterfaceDefinition) {
                continue;
            }

            /** @var ClassDefinition $definition */
            if (in_array($tag, $definition->getTags(), strict: true)) {
                $taggedDefinitions[] = $definition;
            }
        }

        return $taggedDefinitions;
    }

    public function has(string $id): bool
    {
        return isset($this->definitions[$id]) || $this->findOneByAlias($id);
    }

    private function findOneByAlias(string $alias): ?DefinitionInterface
    {
        foreach ($this->definitions as $definition) {
            if ($definition instanceof InterfaceDefinition) {
                continue;
            }

            /** @var ClassDefinition $definition */
            if (in_array($alias, $definition->getAliases(), strict: true)) {
                return $definition;
            }
        }

        return null;
    }

    private function getRootDecoratorDefinition(DefinitionInterface $definition): DefinitionInterface
    {
        while ($definition->getDecoratedBy()) {
            /** @psalm-suppress PossiblyNullArrayOffset */
            $definition = $this->definitions[$definition->getDecoratedBy()];
        }

        return $definition;
    }

    private function resolveDecorators(DefinitionInterface $definition): DefinitionInterface
    {
        if ($definition instanceof InterfaceDefinition) {
            if (!$definition->getDecoratedBy()) {
                return $this->definitions[$definition->getImplementedById()];
            }

            return $this->getRootDecoratorDefinition($definition);
        }

        /** @var ClassDefinition $definition */
        $decoratedBy = $definition->getDecoratedBy();
        $decorates = $definition->getDecorates();

        $isDecorationRoot = $decoratedBy && !$decorates;
        if (!$isDecorationRoot) {
            return $definition;
        }

        return $this->getRootDecoratorDefinition($definition);
    }
}
