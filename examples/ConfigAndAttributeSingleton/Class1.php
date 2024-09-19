<?php

declare(strict_types=1);

namespace Example\ConfigAndAttributeSingleton;

use Temkaa\Container\Attribute\Autowire;

#[Autowire(singleton: false)]
final readonly class Class1
{
}
