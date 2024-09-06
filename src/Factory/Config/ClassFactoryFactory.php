<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Factory\Config;

use Temkaa\SimpleContainer\Attribute\Bind\InstanceOfIterator;
use Temkaa\SimpleContainer\Attribute\Bind\TaggedIterator;
use Temkaa\SimpleContainer\Attribute\Factory as FactoryAttribute;
use Temkaa\SimpleContainer\Model\Config\Factory;
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
