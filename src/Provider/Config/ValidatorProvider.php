<?php

declare(strict_types=1);

namespace Temkaa\Container\Provider\Config;

use Temkaa\Container\Validator\Config\ClassBindingValidator;
use Temkaa\Container\Validator\Config\InterfaceBindingValidator;
use Temkaa\Container\Validator\Config\PathValidator;
use Temkaa\Container\Validator\Config\ValidatorInterface;
use Temkaa\Container\Validator\Config\VariableBindingValidator;

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
            new PathValidator(),
            new VariableBindingValidator(),
            new ClassBindingValidator(),
            new InterfaceBindingValidator(),
        ];
    }
}
