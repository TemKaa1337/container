<?php

declare(strict_types=1);

namespace Temkaa\Container\Factory\Config;

use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Temkaa\Container\Attribute\Factory as FactoryAttribute;
use Temkaa\Container\Model\Config\Factory;
use UnitEnum;

/**
 * @internal
 */
final class ClassFactoryFactory
{
    /**
     * @param class-string                                                     $id
     * @param string                                                           $method
     * @param array<string, string|InstanceOfIterator|TaggedIterator|UnitEnum> $boundVariables
     *
     * @return Factory
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
