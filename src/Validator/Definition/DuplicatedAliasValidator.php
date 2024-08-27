<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Definition;

use Temkaa\SimpleContainer\Exception\DuplicatedEntryAliasException;
use Temkaa\SimpleContainer\Model\Definition\Bag;
use Temkaa\SimpleContainer\Model\Definition\ClassDefinition;

/**
 * @internal
 */
final readonly class DuplicatedAliasValidator
{
    public function validate(Bag $definitions): void
    {
        $uniqueAliases = [];

        foreach ($definitions as $definition) {
            if (!$definition instanceof ClassDefinition) {
                continue;
            }

            foreach ($definition->getAliases() as $alias) {
                if (isset($uniqueAliases[$alias])) {
                    throw new DuplicatedEntryAliasException($alias, $uniqueAliases[$alias], $definition->getId());
                }

                $uniqueAliases[$alias] = $definition->getId();
            }
        }
    }
}
