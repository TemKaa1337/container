<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\BusinessLocation;

final readonly class BusinessLocationFactory
{
    public function __construct(private LocationFactory $locationFactory)
    {
    }

    public function create(array $message): BusinessLocation
    {
        return new BusinessLocation(
            $message['address'],
            isset($message['location']) ? $this->locationFactory->create($message['location']) : null,
        );
    }
}
