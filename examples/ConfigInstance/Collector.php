<?php

declare(strict_types=1);

namespace Example\ConfigInstance;

final readonly class Collector
{
    public function __construct(
        public Interface1 $object,
    ) {
    }
}
