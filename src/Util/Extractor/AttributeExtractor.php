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
     * @return T
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
     *
     * @return string[]
     */
    public static function extractParameters(array $attributes, string $parameter): array
    {
        /** @psalm-suppress MixedReturnStatement, MixedInferredReturnType */
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
        return (bool) array_filter(
            $attributes,
            static fn (ReflectionAttribute $attribute): bool => $attribute->newInstance()->{$parameter} === $value,
        );
    }
}
