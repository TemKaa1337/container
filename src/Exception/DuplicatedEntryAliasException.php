<?php

declare(strict_types=1);

namespace Temkaa\Container\Exception;

use LogicException;
use function sprintf;

/**
 * @api
 */
final class DuplicatedEntryAliasException extends LogicException
{
    public function __construct(string $alias, string $foundInId, string $currentId)
    {
        parent::__construct(
            message: sprintf(
                'Could not compile container as there are duplicated alias "%s" in class "%s", found in "%s".',
                $alias,
                $currentId,
                $foundInId,
            ),
        );
    }
}
