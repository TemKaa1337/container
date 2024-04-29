<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator;

use Temkaa\SimpleContainer\Exception\DuplicatedEntryAliasException;
use Temkaa\SimpleContainer\Model\Definition;

final readonly class DuplicatedDefinitionAliasValidator
{
    /**
     * @param Definition[] $definitions
     */
    public function validate(array $definitions): void
    {
        $uniqueDefinitions = [];
        $uniqueAliases = [];

        foreach ($definitions as $definition) {
            if (isset($uniqueDefinitions[$definition->getId()])) {
                continue;
            }

            $uniqueDefinitions[$definition->getId()] = true;

            foreach ($definition->getAliases() as $alias) {
                if (isset($uniqueAliases[$alias])) {
                    throw new DuplicatedEntryAliasException($alias, $uniqueAliases[$alias], $definition->getId());
                }

                $uniqueAliases[$alias] = $definition->getId();
            }
        }
    }
}
