<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Exception;

use RuntimeException;
use Psr\Container\ContainerExceptionInterface;

final class EntryNotFoundException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Entry "%s" not found.', $id));
    }
}
