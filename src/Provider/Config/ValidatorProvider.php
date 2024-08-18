<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Provider\Config;

use Temkaa\SimpleContainer\Validator\Config\ClassBindingNodeValidator;
use Temkaa\SimpleContainer\Validator\Config\InterfaceBindingNodeValidator;
use Temkaa\SimpleContainer\Validator\Config\ServicesNodeValidator;
use Temkaa\SimpleContainer\Validator\Config\ValidatorInterface;
use Temkaa\SimpleContainer\Validator\Config\VariableBindValidator;

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
            new ServicesNodeValidator(),
            new VariableBindValidator(),
            new ClassBindingNodeValidator(),
            new InterfaceBindingNodeValidator(),
        ];
    }
}
