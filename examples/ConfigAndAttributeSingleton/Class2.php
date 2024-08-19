<?php

declare(strict_types=1);

namespace Example\ConfigAndAttributeSingleton;

final readonly class Class2
{
    public function __construct(
        public Class1 $class,
    ) {
    }
}
