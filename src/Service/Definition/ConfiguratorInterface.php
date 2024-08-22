<?php

declare(strict_types=1);

namespace Temkaa\SimpleContainer\Service\Definition;

use Temkaa\SimpleContainer\Model\Definition\Bag;

interface ConfiguratorInterface
{
    public function configure(): Bag;
}
