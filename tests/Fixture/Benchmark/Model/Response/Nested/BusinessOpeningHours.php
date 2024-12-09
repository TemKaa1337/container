<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class BusinessOpeningHours
{
    /**
     * @param BusinessOpeningHoursInterval[] $openingHours
     */
    public function __construct(
        public string $timeZoneName,
        public array $openingHours,
    ) {
    }
}
