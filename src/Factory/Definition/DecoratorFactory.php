<?php

declare(strict_types=1);

namespace Temkaa\Container\Factory\Definition;

use Temkaa\Container\Attribute\Decorates;
use Temkaa\Container\Model\Config\Decorator;

/**
 * @internal
 */
final readonly class DecoratorFactory
{
    /**
     * @param class-string $id
     */
    public static function create(
        string $id,
        int $priority = Decorator::DEFAULT_PRIORITY,
    ): Decorator {
        return new Decorator($id, $priority);
    }

    public static function createFromAttribute(Decorates $attribute): Decorator
    {
        return self::create($attribute->id, $attribute->priority);
    }
}
