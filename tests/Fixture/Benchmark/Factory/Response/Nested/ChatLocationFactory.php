<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\ChatLocation;

final readonly class ChatLocationFactory
{
    public function __construct(private LocationFactory $locationFactory)
    {
    }

    public function create(array $message): ChatLocation
    {
        return new ChatLocation(
            $this->locationFactory->create($message['location']),
            $message['address'],
        );
    }
}
