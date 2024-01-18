<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

final class UnresolvableArgumentException extends RuntimeException implements ContainerExceptionInterface
{
}
