<?php

declare(strict_types=1);

namespace Example\ConfigAndAttributeSingleton;

use Temkaa\SimpleContainer\Attribute\Autowire;

#[Autowire(singleton: false)]
final readonly class Class1
{
}
