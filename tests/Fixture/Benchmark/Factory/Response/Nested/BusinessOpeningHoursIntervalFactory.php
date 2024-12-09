<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\BusinessOpeningHoursInterval;

final readonly class BusinessOpeningHoursIntervalFactory
{
    public function create(array $message): BusinessOpeningHoursInterval
    {
        return new BusinessOpeningHoursInterval(
            $message['opening_minute'],
            $message['closing_minute'],
        );
    }
}
