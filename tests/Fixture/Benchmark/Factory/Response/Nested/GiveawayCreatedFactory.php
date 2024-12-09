<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\GiveawayCreated;

final readonly class GiveawayCreatedFactory
{
    public function create(array $message): GiveawayCreated
    {
        return new GiveawayCreated(
            $message['prize_star_count'] ?? null,
        );
    }
}
