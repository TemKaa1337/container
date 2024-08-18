<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Temkaa\SimpleContainer\Model\Container\ConfigNew;

/**
 * @internal
 */
interface ValidatorInterface
{
    public function validate(ConfigNew $config): void;
}
