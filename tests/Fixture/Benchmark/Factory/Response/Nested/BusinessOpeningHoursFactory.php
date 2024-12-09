<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\BusinessOpeningHours;
use Tests\Fixture\Benchmark\Model\Response\Nested\BusinessOpeningHoursInterval;

final readonly class BusinessOpeningHoursFactory
{
    public function __construct(private BusinessOpeningHoursIntervalFactory $businessOpeningHoursIntervalFactory)
    {
    }

    public function create(array $message): BusinessOpeningHours
    {
        return new BusinessOpeningHours(
            $message['time_zone_name'],
            array_map(
                fn (array $nested): BusinessOpeningHoursInterval => $this->businessOpeningHoursIntervalFactory->create(
                    $nested,
                ),
                $message['opening_hours'],
            ),
        );
    }
}
