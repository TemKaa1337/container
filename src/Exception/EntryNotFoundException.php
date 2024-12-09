<?php

declare(strict_types=1);

namespace Temkaa\Container\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;
use function sprintf;

/**
 * @api
 */
final class EntryNotFoundException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Entry "%s" not found.', $id));
    }
}
