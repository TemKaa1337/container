<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Exception;

use RuntimeException;
use Psr\Container\ContainerExceptionInterface;

final class UnresolvableArgumentException extends RuntimeException implements ContainerExceptionInterface
{
}
