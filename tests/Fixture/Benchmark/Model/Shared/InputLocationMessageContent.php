<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InputLocationMessageContent
{
    use ArrayFilterTrait;

    public function __construct(
        public float $latitude,
        public float $longitude,
        public ?float $horizontalAccuracy = null,
        public ?int $livePeriod = null,
        public ?int $heading = null,
        public ?int $proximityAlertRadius = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'latitude'               => $this->latitude,
                'longitude'              => $this->longitude,
                'horizontal_accuracy'    => $this->horizontalAccuracy,
                'live_period'            => $this->livePeriod,
                'heading'                => $this->heading,
                'proximity_alert_radius' => $this->proximityAlertRadius,
            ],
        );
    }
}
