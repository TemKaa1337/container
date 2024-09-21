<?php

declare(strict_types=1);

namespace Temkaa\Container\Provider\Config;

use Temkaa\Container\Model\Config;

interface ProviderInterface
{
    public function provide(): Config;
}
