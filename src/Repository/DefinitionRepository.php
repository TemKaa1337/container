<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Repository;

use Temkaa\SimpleContainer\Exception\EntryNotFoundException;
use Temkaa\SimpleContainer\Model\Definition;

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
            return $entry;
        }

        if ($entry = $this->findByAlias($id)) {
            return $entry;
        }

        throw new EntryNotFoundException(sprintf('Could not find entry "%s".', $id));
    }

    public function has(string $id): bool
    {
        if (isset($this->definitions[$id])) {
            return true;
        }

        return (bool) $this->findByAlias($id);
    }

    private function findByAlias(string $alias): ?Definition
    {
        foreach ($this->definitions as $definition) {
            if (in_array($alias, $definition->getAliases(), strict: true)) {
                return $definition;
            }
        }

        return null;
    }
}
