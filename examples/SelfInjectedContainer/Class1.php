<?php

declare(strict_types=1);

namespace Example\SelfInjectedContainer;

use Psr\Container\ContainerInterface;
use Temkaa\Container\Container;

final readonly class Class1
{
    public function __construct(
        private Container $containerFromClass,
        private ContainerInterface $containerFromInterface,
    ) {
    }
}
