<?php

declare(strict_types=1);

namespace Temkaa\Container\Util;

use Temkaa\Container\Attribute\Bind\InstanceOfIterator;
use Temkaa\Container\Attribute\Bind\TaggedIterator;
use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\Factory;
use UnitEnum;

/**
 * @internal
 */
final class BoundVariableProvider
{
    /**
     * @param Config       $config
     * @param string       $argumentName
     * @param class-string $id
     * @param Factory|null $factory
     *
     * @return null|string|InstanceOfIterator|TaggedIterator|UnitEnum
     */
    public static function provide(
        Config $config,
        string $argumentName,
        string $id,
        ?Factory $factory,
    ): null|string|InstanceOfIterator|TaggedIterator|UnitEnum {
        $classBinding = $config->getBoundedClass($id);
        $classBoundVars = $classBinding?->getBoundedVariables() ?? [];
        $classFactoryBindings = $factory?->getBoundedVariables() ?? [];

        $globalBoundVars = $config->getBoundedVariables();

        return $factory
            ? $classFactoryBindings[$argumentName] ?? $globalBoundVars[$argumentName] ?? null
            : $classBoundVars[$argumentName] ?? $globalBoundVars[$argumentName] ?? null;
    }
}
