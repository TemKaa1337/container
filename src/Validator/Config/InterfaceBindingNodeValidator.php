<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use Temkaa\SimpleContainer\Enum\Config\Structure;
use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Exception\Config\CannotBindInterfaceException;
use Temkaa\SimpleContainer\Exception\Config\InvalidConfigNodeTypeException;

final class InterfaceBindingNodeValidator implements ValidatorInterface
{
    /**
     * @psalm-suppress ArgumentTypeCoercion
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @throws ContainerExceptionInterface
     */
    public function validate(array $config): void
    {
        /** @var class-string $nodeName */
        /** @var class-string $nodeValue */
        foreach ($config[Structure::Services->value] ?? [] as $nodeName => $nodeValue) {
            if (!is_string($nodeName)) {
                throw new InvalidConfigNodeTypeException(
                    'Node "services.{className|interfaceName}" must be of "array<string, array|string>" type.',
                );
            }

            if (Structure::tryFrom($nodeName)) {
                continue;
            }

            if (!class_exists($nodeName) && !interface_exists($nodeName)) {
                throw new ClassNotFoundException($nodeName);
            }

            if (class_exists($nodeName)) {
                continue;
            }

            if (!interface_exists($nodeName)) {
                throw new ClassNotFoundException($nodeName);
            }

            if (!class_exists($nodeValue)) {
                throw new ClassNotFoundException($nodeValue);
            }

            $reflection = new ReflectionClass($nodeName);
            if (!$reflection->isInterface()) {
                throw new CannotBindInterfaceException(
                    sprintf('Cannot bind interface "%s" as it not an interface.', $nodeName),
                );
            }

            $reflection = new ReflectionClass($nodeValue);
            if ($reflection->isInterface()) {
                throw new CannotBindInterfaceException(
                    sprintf(
                        'Cannot bind interface "%s" to class "%s" as it it as interface.',
                        $nodeName,
                        $nodeValue,
                    ),
                );
            }

            if (!$reflection->implementsInterface($nodeName)) {
                throw new CannotBindInterfaceException(
                    sprintf(
                        'Cannot bind interface "%s" to class "%s" as it doesn\'t implement it.',
                        $nodeName,
                        $nodeValue,
                    ),
                );
            }
        }
    }
}
