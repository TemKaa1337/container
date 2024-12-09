<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class ShippingAddress
{
    public function __construct(
        public string $countryCode,
        public string $state,
        public string $city,
        public string $streetLine1,
        public string $streetLine2,
        public string $postCode,
    ) {
    }
}
