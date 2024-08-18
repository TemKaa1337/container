<?php

declare(strict_types=1);

namespace Example\Example6;

use Temkaa\SimpleContainer\Attribute\Decorates;

#[Decorates(id: Interface1::class, priority: 1, signature: 'class')]
final readonly class Class3 implements Interface1
{
    public function __construct(
        public Interface1 $class,
    ) {
    }
}