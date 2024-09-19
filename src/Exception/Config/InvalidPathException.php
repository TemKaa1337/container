<?php

declare(strict_types=1);

namespace Temkaa\Container\Exception\Config;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

final class InvalidPathException extends RuntimeException implements ContainerExceptionInterface
{
}
