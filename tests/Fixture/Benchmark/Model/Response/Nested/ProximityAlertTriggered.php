<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class ProximityAlertTriggered
{
    public function __construct(
        public User $traveler,
        public User $watcher,
        public int $distance,
    ) {
    }
}
