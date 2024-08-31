<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Factory\Config;

use Temkaa\SimpleContainer\Attribute\Factory as FactoryAttribute;
use Temkaa\SimpleContainer\Model\Config\Factory;

final class ClassFactoryFactory
{
    /**
     * @param class-string $id
     */
    public static function create(string $id, string $method, array $boundVariables): Factory
    {
        return new Factory($id, $method, $boundVariables);
    }

    public static function createFromAttribute(FactoryAttribute $factory): Factory
    {
        return self::create($factory->id, $factory->method, boundVariables: []);
    }
}
