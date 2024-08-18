<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Util\Extractor;

use ReflectionAttribute;

/**
 * @internal
 */
final class AttributeExtractor
{
    /**
     * @template T of object
     *
     * @param ReflectionAttribute<T>[] $attributes
     * @param int                      $index
     *
     * @return object
     */
    public static function extract(array $attributes, int $index): object
    {
        $attributes = array_map(
            static fn (ReflectionAttribute $attribute): object => $attribute->newInstance(),
            $attributes,
        );

        return $attributes[$index];
    }

    /**
     * @template T of object
     *
     * @param ReflectionAttribute<T>[] $attributes
     */
    public static function extractParameters(array $attributes, string $parameter): array
    {
        return array_map(
            static fn (ReflectionAttribute $attribute): string => $attribute->newInstance()->{$parameter},
            $attributes,
        );
    }

    /**
     * @template T of object
     *
     * @param ReflectionAttribute<T>[] $attributes
     */
    public static function hasParameterByValue(array $attributes, string $parameter, mixed $value): bool
    {
        $filtered = array_filter(
            $attributes,
            static fn (ReflectionAttribute $attribute): bool => $attribute->newInstance()->{$parameter} === $value,
        );

        return (bool) array_values($filtered);
    }
}
