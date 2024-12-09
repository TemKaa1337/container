<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ProximityAlertTriggered;

final readonly class ProximityAlertTriggeredFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): ProximityAlertTriggered
    {
        return new ProximityAlertTriggered(
            $this->userFactory->create($message['traveler']),
            $this->userFactory->create($message['watcher']),
            $message['distance'],
        );
    }
}
