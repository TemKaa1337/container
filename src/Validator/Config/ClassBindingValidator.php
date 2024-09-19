<?php

declare(strict_types=1);

namespace Temkaa\Container\Validator\Config;

use Temkaa\Container\Exception\ClassNotFoundException;
use Temkaa\Container\Model\Config;

/**
 * @internal
 */
final class ClassBindingValidator implements ValidatorInterface
{
    public function validate(Config $config): void
    {
        foreach ($config->getBoundedClasses() as $classConfig) {
            if (!class_exists($classConfig->getClass()) && !interface_exists($classConfig->getClass())) {
                throw new ClassNotFoundException($classConfig->getClass());
            }
        }
    }
}
