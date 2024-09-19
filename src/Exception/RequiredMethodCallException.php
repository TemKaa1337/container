<?php

declare(strict_types=1);

namespace Temkaa\Container\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

final class RequiredMethodCallException extends RuntimeException implements ContainerExceptionInterface
{
}
