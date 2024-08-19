<?php

declare(strict_types=1);

namespace Example\AttributeDecorator;

use Temkaa\SimpleContainer\Attribute\Decorates;

#[Decorates(id: Interface1::class, priority: 2)]
final readonly class Class2 implements Interface1
{
    public function __construct(
        public Interface1 $inner,
    ) {
    }
}
