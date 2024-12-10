<?php

declare(strict_types=1);

namespace Temkaa\Container\Service;

use ReflectionClass;

final class CachingReflector
{
    private static array $cache = [];

    public static int $cacheHits = 0;

    public static function reflect(string $className): object
    {
        // $reflection = new ReflectionClass($className);
        //
        // return self::$cache[$className] = $reflection;
        if (!isset(self::$cache[$className])) {
            $reflection = new ReflectionClass($className);

            return self::$cache[$className] = $reflection;
        }

        self::$cacheHits ++;

        return self::$cache[$className];
    }
}
