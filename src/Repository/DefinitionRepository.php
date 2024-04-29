<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Repository;

use Temkaa\SimpleContainer\Attribute\Autowire;
use Temkaa\SimpleContainer\Exception\EntryNotFoundException;
use Temkaa\SimpleContainer\Model\Definition;

#[Autowire(load: false)]
final readonly class DefinitionRepository
{
    /**
     * @var array<class-string, Definition>
     */
    private array $definitions;

    /**
     * @param Definition[] $definitions
     */
    public function __construct(array $definitions)
    {
        $this->definitions = array_combine(
            array_map(
                static fn (Definition $definition): string => $definition->getId(),
                $definitions,
            ),
            $definitions,
        );
    }

    public function find(string $id): Definition
    {
        if ($entry = $this->definitions[$id] ?? null) {
            return $this->resolveDecorators($id, $entry);
        }

        if ($entry = $this->findOneByAlias($id)) {
            return $this->resolveDecorators($id, $entry);
        }

        throw new EntryNotFoundException(sprintf('Could not find entry "%s".', $id));
    }

    /**
     * @return Definition[]
     */
    public function findAllByTag(string $tag): array
    {
        $taggedDefinitions = [];
        foreach ($this->definitions as $definition) {
            if (in_array($tag, $definition->getTags(), strict: true)) {
                $taggedDefinitions[] = $definition;
            }
        }

        return $taggedDefinitions;
    }

    public function has(string $id): bool
    {
        if (isset($this->definitions[$id])) {
            return true;
        }

        return (bool) $this->findOneByAlias($id);
    }

    private function findOneByAlias(string $alias): ?Definition
    {
        foreach ($this->definitions as $definition) {
            if (in_array($alias, $definition->getAliases(), strict: true)) {
                return $definition;
            }
        }

        return null;
    }

    private function resolveDecorators(string $searchedById, Definition $definition): Definition
    {
        // TODO: add tests on when something is requiring not root decorated service
        // TODO: refactor this condition
        $decoratedBy = $definition->getDecoratedBy();
        $decorates = $definition->getDecorates();

        $isDecorationRoot = $decoratedBy && !$decorates;
        if (!$isDecorationRoot) {
            return $definition;
        }

        /** @psalm-suppress PossiblyNullArrayOffset */
        $isDecoratedByInterface = $this->definitions[$definition->getDecoratedBy()]->getDecorates()->isByInterface();
        if ($isDecoratedByInterface && !interface_exists($searchedById)) {
            return $definition;
        }

        $currentDefinition = $definition;
        while ($currentDefinition->getDecoratedBy()) {
            /** @psalm-suppress PossiblyNullArrayOffset */
            $currentDefinition = $this->definitions[$currentDefinition->getDecoratedBy()];
        }

        return $currentDefinition;
    }
}
