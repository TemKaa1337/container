<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Util;

use ReflectionAttribute;

final class AttributeExtractor
{
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
}
