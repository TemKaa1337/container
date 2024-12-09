<?php

declare(strict_types=1);

namespace Temkaa\Container\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * @api
 */
final class NonAutowirableClassException extends RuntimeException implements ContainerExceptionInterface
{
}
