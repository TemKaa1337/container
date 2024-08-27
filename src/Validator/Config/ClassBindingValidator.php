<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Temkaa\SimpleContainer\Exception\ClassNotFoundException;
use Temkaa\SimpleContainer\Model\Config;

/**
 * @internal
 */
final class ClassBindingValidator implements ValidatorInterface
{
    public function validate(Config $config): void
    {
        foreach ($config->getBoundedClasses() as $classConfig) {
            if (!class_exists($classConfig->getClass())) {
                throw new ClassNotFoundException($classConfig->getClass());
            }
        }
    }
}
