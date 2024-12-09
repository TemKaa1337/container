<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class Invoice
{
    public function __construct(
        public string $title,
        public string $description,
        public string $startParameter,
        public string $currency,
        public int $totalAmount,
    ) {
    }
}
