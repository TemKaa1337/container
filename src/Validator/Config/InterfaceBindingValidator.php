<?php

declare(strict_types=1);

namespace Temkaa\Container\Validator\Config;

use ReflectionClass;
use Temkaa\Container\Exception\ClassNotFoundException;
use Temkaa\Container\Exception\Config\CannotBindInterfaceException;
use Temkaa\Container\Model\Config;
use function class_exists;
use function interface_exists;
use function sprintf;

/**
 * @internal
 */
final readonly class InterfaceBindingValidator implements ValidatorInterface
{
    public function validate(Config $config): void
    {
        foreach ($config->getBoundedInterfaces() as $interface => $class) {
            if (!interface_exists($interface)) {
                throw new ClassNotFoundException($interface, isInterface: true);
            }

            if (!class_exists($class)) {
                throw new ClassNotFoundException($class);
            }

            $reflection = new ReflectionClass($class);
            if (!$reflection->implementsInterface($interface)) {
                throw new CannotBindInterfaceException(
                    sprintf(
                        'Cannot bind class "%s" to interface "%s" as it doesn\'t implement it.',
                        $class,
                        $interface,
                    ),
                );
            }
        }
    }
}
