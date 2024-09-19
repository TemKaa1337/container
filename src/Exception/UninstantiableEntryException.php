<?php

declare(strict_types=1);

namespace Temkaa\Container\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

final class UninstantiableEntryException extends RuntimeException implements ContainerExceptionInterface
{
}
