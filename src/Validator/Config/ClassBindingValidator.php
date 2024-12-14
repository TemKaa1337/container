<?php

declare(strict_types=1);

namespace Temkaa\Container\Validator\Config;

use Temkaa\Container\Exception\ClassNotFoundException;
use Temkaa\Container\Model\Config;
use function class_exists;
use function interface_exists;

/**
 * @internal
 */
final readonly class ClassBindingValidator implements ValidatorInterface
{
    public function validate(Config $config): void
    {
        foreach ($config->getConfiguredClasses() as $classConfig) {
            if (!class_exists($classConfig->getClass()) && !interface_exists($classConfig->getClass())) {
                throw new ClassNotFoundException($classConfig->getClass());
            }
        }
    }
}
