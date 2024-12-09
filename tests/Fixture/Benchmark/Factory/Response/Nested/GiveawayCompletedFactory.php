<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\GiveawayCompleted;

final readonly class GiveawayCompletedFactory
{
    public function __construct(private MessageFactory $messageFactory)
    {
    }

    public function create(array $message): GiveawayCompleted
    {
        return new GiveawayCompleted(
            $message['winner_count'],
            $message['unclaimed_prize_count'] ?? null,
            isset($message['giveaway_message']) ? $this->messageFactory->create($message['giveaway_message']) : null,
            $message['is_star_giveaway'] ?? null,
        );
    }
}
