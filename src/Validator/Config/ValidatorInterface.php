<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Validator\Config;

use Temkaa\SimpleContainer\Model\Config;

/**
 * @internal
 *
 * @psalm-api
 */
interface ValidatorInterface
{
    public function validate(Config $config): void;
}
