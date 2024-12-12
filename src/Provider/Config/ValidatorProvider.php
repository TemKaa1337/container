<?php

declare(strict_types=1);

namespace Temkaa\Container\Provider\Config;

use Temkaa\Container\Validator\Config\ClassBindingValidator;
use Temkaa\Container\Validator\Config\InterfaceBindingValidator;
use Temkaa\Container\Validator\Config\ValidatorInterface;

/**
 * @internal
 */
final class ValidatorProvider
{
    /**
     * @return ValidatorInterface[]
     */
    public function provide(): array
    {
        return [
            new ClassBindingValidator(),
            new InterfaceBindingValidator(),
        ];
    }
}
