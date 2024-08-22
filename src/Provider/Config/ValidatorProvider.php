<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Provider\Config;

use Temkaa\SimpleContainer\Validator\Config\ClassBindingValidator;
use Temkaa\SimpleContainer\Validator\Config\InterfaceBindingValidator;
use Temkaa\SimpleContainer\Validator\Config\PathValidator;
use Temkaa\SimpleContainer\Validator\Config\ValidatorInterface;
use Temkaa\SimpleContainer\Validator\Config\VariableBindingValidator;

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
