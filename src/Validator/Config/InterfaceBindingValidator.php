<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use ReflectionClass;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\CannotBindInterfaceException;
use Temkaa\SimpleContainer\Model\Config;

/**
 * @internal
 */
final class InterfaceBindingValidator implements ValidatorInterface
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
