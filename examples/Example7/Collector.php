<?php

declare(strict_types=1);

namespace Example\Example7;

final readonly class Collector
{
    public function __construct(
        public array $objects,
    ) {
    }
}
