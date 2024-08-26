<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

final class ClassNotFoundException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $class, bool $isInterface = false)
    {
        parent::__construct(sprintf('%s "%s" is not found.', $isInterface ? 'Interface' : 'Class', $class));
    }
}
