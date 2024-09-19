<?php

declare(strict_types=1);

namespace Temkaa\Container\Factory\Definition;

use Temkaa\Container\Model\Definition\Class\Factory;
use Temkaa\Container\Model\Definition\Class\Method;

/**
 * @internal
 */
final class ClassFactoryFactory
{
    /**
     * @param class-string $id
     */
    public static function create(string $id, string $method, array $arguments, bool $isStatic): Factory
    {
        return new Factory($id, new Method($method, $arguments, $isStatic));
    }
}
