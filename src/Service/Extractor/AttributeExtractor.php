<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Extractor;

use ReflectionAttribute;
use function array_filter;
use function array_map;

/**
 * @internal
 */
final readonly class AttributeExtractor
{
    /**
     * @template T of object
     *
     * @param ReflectionAttribute<T>[] $attributes
     *
     * @return T
     */
    public function extract(array $attributes, int $index): object
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
    public function extractParameters(array $attributes, string $parameter): array
    {
        /** @psalm-suppress MixedReturnStatement, MixedInferredReturnType */
        return array_map(
            static fn (ReflectionAttribute $attribute): mixed => $attribute->newInstance()->{$parameter},
            $attributes,
        );
    }

    /**
     * @template T of object
     *
     * @param ReflectionAttribute<T>[] $attributes
     */
    public function hasParameterByValue(array $attributes, string $parameter, mixed $value): bool
    {
        return (bool) array_filter(
            $attributes,
            static fn (ReflectionAttribute $attribute): bool => $attribute->newInstance()->{$parameter} === $value,
        );
    }
}
