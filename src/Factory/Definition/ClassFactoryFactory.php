<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Factory\Definition;

use Temkaa\SimpleContainer\Model\Definition\Class\Factory;
use Temkaa\SimpleContainer\Model\Definition\Class\Method;

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
