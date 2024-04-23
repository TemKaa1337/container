<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

interface ValidatorInterface
{
    public function validate(array $config): void;
}
