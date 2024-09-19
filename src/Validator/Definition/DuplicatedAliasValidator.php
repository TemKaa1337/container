<?php

declare(strict_types=1);

namespace Temkaa\Container\Validator\Definition;

use Temkaa\Container\Exception\DuplicatedEntryAliasException;
use Temkaa\Container\Model\Definition\Bag;
use Temkaa\Container\Model\Definition\ClassDefinition;

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
