<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Factory\Definition;

use Temkaa\SimpleContainer\Attribute\Decorates;
use Temkaa\SimpleContainer\Model\Config\Decorator;

final class DecoratorFactory
{
    /**
     * @param class-string $id
     */
    public static function create(
        string $id,
        int $priority = Decorator::DEFAULT_PRIORITY,
        string $signature = Decorator::DEFAULT_SIGNATURE,
    ): Decorator {
        return new Decorator($id, $priority, str_replace('$', '', $signature));
    }

    public static function createFromAttribute(Decorates $attribute): Decorator
    {
        return self::create($attribute->id, $attribute->priority, $attribute->signature);
    }
}
