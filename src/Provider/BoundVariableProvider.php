<?php

declare(strict_types=1);

namespace Temkaa\Container\Provider;

use Temkaa\Container\Model\Config;
use Temkaa\Container\Model\Config\Factory;
use Temkaa\Container\Model\Value;
use function array_key_exists;

/**
 * @internal
 */
final readonly class BoundVariableProvider
{
    /**
     * @param class-string $id
     */
    public function provide(
        Config $config,
        string $argumentName,
        string $id,
        ?Factory $factory,
    ): Value {
        $classConfiguration = $config->getConfiguredClass($id);
        $classBoundVars = $classConfiguration?->getBoundedVariables() ?? [];
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
