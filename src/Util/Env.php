<?php

declare(strict_types=1);

namespace Temkaa\Container\Util;

use function getenv;

/**
 * @internal
 */
final readonly class Env
{
    public static function get(string $name): string
    {
        return (string) getenv($name);
    }

    public static function has(string $name): bool
    {
        return getenv($name) !== false;
    }
}
