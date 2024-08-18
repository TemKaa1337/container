<?php

declare(strict_types=1);

namespace Example\Example10;

use Temkaa\SimpleContainer\Attribute\Autowire;

#[Autowire(singleton: false)]
final readonly class Class1
{
}
