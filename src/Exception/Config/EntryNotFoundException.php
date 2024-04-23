<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Exception\Config;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

final class EntryNotFoundException extends RuntimeException implements ContainerExceptionInterface
{
}
