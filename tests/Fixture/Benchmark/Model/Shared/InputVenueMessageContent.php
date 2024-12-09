<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InputVenueMessageContent
{
    use ArrayFilterTrait;

    public function __construct(
        public float $latitude,
        public float $longitude,
        public string $title,
        public string $address,
        public ?string $foursquareId = null,
        public ?string $foursquareType = null,
        public ?string $googlePlaceId = null,
        public ?string $googlePlaceType = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'latitude'          => $this->latitude,
                'longitude'         => $this->longitude,
                'title'             => $this->title,
                'address'           => $this->address,
                'foursquare_id'     => $this->foursquareId,
                'foursquare_type'   => $this->foursquareType,
                'google_place_id'   => $this->googlePlaceId,
                'google_place_type' => $this->googlePlaceType,
            ],
        );
    }
}
