<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class BusinessLocation
{
    public function __construct(
        public string $address,
        public ?Location $location = null,
    ) {
    }
}
