<?php

declare(strict_types=1);

namespace Temkaa\Container\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * @api
 */
final class UnresolvableArgumentException extends RuntimeException implements ContainerExceptionInterface
{
}
