<?php

declare(strict_types=1);

namespace Temkaa\Container\Service\Definition;

use Temkaa\Container\Model\Definition\Bag;

interface ConfiguratorInterface
{
    public function configure(): Bag;
}
