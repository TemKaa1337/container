<?php

declare(strict_types=1);

namespace Example\ConfigTaggedIterator;

final readonly class Collector
{
    public function __construct(
        public array $objects,
    ) {
    }
}
