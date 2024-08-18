<?php

declare(strict_types=1);

namespace Example\Example5;

final readonly class Collector
{
    public function __construct(
        public Interface1 $class,
    ) {
    }
}
