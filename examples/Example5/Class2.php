<?php

declare(strict_types=1);

namespace Example\Example5;

final readonly class Class2 implements Interface1
{
    public function __construct(
        public Interface1 $inner,
    ) {
    }
}
