<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\CannotBindInterfaceException;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;

final class InterfaceBindingNodeValidator
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function validate(array $config): void
    {
        if (isset($config['interface_bindings'])) {
            if (!is_array($config['interface_bindings'])) {
                throw new InvalidConfigNodeTypeException(
                    'Node "interface_bindings" must be of "array<string, string>" type.',
                );
            }

            foreach ($config['interface_bindings'] as $interfaceName => $className) {
                if (!is_string($interfaceName) || !is_string($className)) {
                    throw new InvalidConfigNodeTypeException(
                        'Node "interface_bindings" must be of "array<string, string>" type.',
                    );
                }
            }

            foreach ($config['interface_bindings'] as $interfaceName => $className) {
                if (!$interfaceName || !$className) {
                    throw new CannotBindInterfaceException(
                        sprintf('Cannot bind interface "%s" to interface "%s".', $interfaceName, $className),
                    );
                }

                if (!interface_exists($interfaceName)) {
                    throw new ClassNotFoundException($interfaceName);
                }

                if (!class_exists($className)) {
                    throw new ClassNotFoundException($className);
                }

                $r = new ReflectionClass($interfaceName);
                if (!$r->isInterface()) {
                    throw new CannotBindInterfaceException(
                        sprintf('Cannot bind interface "%s" as it not an interface.', $interfaceName),
                    );
                }

                $r = new ReflectionClass($className);
                if ($r->isInterface()) {
                    throw new CannotBindInterfaceException(
                        sprintf(
                            'Cannot bind interface "%s" to class "%s" as it it as interface.',
                            $interfaceName,
                            $className,
                        ),
                    );
                }

                if (!$r->implementsInterface($interfaceName)) {
                    throw new CannotBindInterfaceException(
                        sprintf(
                            'Cannot bind interface "%s" to class "%s" as it doesn\'t implement int.',
                            $interfaceName,
                            $className,
                        ),
                    );
                }
            }
        }
    }
}
