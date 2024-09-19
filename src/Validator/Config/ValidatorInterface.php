<?php

declare(strict_types=1);

namespace Temkaa\Container\Validator\Config;

use Temkaa\Container\Model\Config;

/**
 * @internal
 *
 * @psalm-api
 */
interface ValidatorInterface
{
    public function validate(Config $config): void;
}
