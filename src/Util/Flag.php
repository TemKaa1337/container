<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Util;

/**
 * @internal
 */
final class Flag
{
    private static array $flags = [];

    public static function isToggled(string $name, string $group): bool
    {
        return isset(self::$flags[$group][$name]);
    }

    public static function toggle(string $name, string $group): void
    {
        self::$flags[$group][$name] = true;
    }

    public static function untoggle(string $name, string $group): void
    {
        unset(self::$flags[$group][$name]);
    }

    public static function getToggled(string $group): array
    {
        return array_keys(self::$flags[$group]);
    }

    public static function clear(): void
    {
        self::$flags = [];
    }
}
