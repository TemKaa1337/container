<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Venue;

final readonly class VenueFactory
{
    public function __construct(private LocationFactory $locationFactory)
    {
    }

    public function create(array $message): Venue
    {
        return new Venue(
            $this->locationFactory->create($message['location']),
            $message['title'],
            $message['address'],
            $message['foursquare_id'] ?? null,
            $message['foursquare_type'] ?? null,
            $message['google_place_id'] ?? null,
            $message['google_place_type'] ?? null,
        );
    }
}
