<?php

declare(strict_types=1);

namespace Temkaa\Container\Util;

use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\Factory;
use Temkaa\Container\Model\Value;
use function array_key_exists;

/**
 * @internal
 */
final class BoundVariableProvider
{
    /**
     * @param class-string $id
     */
    public static function provide(
        Config $config,
        string $argumentName,
        string $id,
        ?Factory $factory,
    ): Value {
        $classBinding = $config->getBoundedClass($id);
        $classBoundVars = $classBinding?->getBoundedVariables() ?? [];
        $classFactoryBindings = $factory?->getBoundedVariables() ?? [];

        $globalBoundVars = $config->getBoundedVariables();

        if ($factory) {
            return match (true) {
                array_key_exists($argumentName, $classFactoryBindings) => new Value(
                    $classFactoryBindings[$argumentName],
                    resolved: true,
                ),
                array_key_exists($argumentName, $globalBoundVars)      => new Value(
                    $globalBoundVars[$argumentName],
                    resolved: true,
                ),
                default                                                => new Value(null, resolved: false),
            };
        }

        return match (true) {
            array_key_exists($argumentName, $classBoundVars)  => new Value(
                $classBoundVars[$argumentName],
                resolved: true,
            ),
            array_key_exists($argumentName, $globalBoundVars) => new Value(
                $globalBoundVars[$argumentName],
                resolved: true,
            ),
            default                                           => new Value(null, resolved: false),
        };
    }
}
