<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Provider\Config;

use Temkaa\SimpleContainer\Validator\Config\ClassBindingNodeValidator;
use Temkaa\SimpleContainer\Validator\Config\InterfaceBindingNodeValidator;
use Temkaa\SimpleContainer\Validator\Config\ServicesNodeValidator;
use Temkaa\SimpleContainer\Validator\Config\ValidatorInterface;

final class ValidatorProvider
{
    /**
     * @return ValidatorInterface[]
     */
    public function provide(): array
    {
        return [
            new ClassBindingNodeValidator(),
            new InterfaceBindingNodeValidator(),
            new ServicesNodeValidator(),
        ];
    }
}
