<?php

declare(strict_types=1);

namespace Temkaa\Container\Exception\Config;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * @api
 */
final class EntryNotFoundException extends RuntimeException implements ContainerExceptionInterface
{
}
