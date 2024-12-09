<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatBoostSourceGiveaway;

final readonly class ChatBoostSourceGiveawayFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): ChatBoostSourceGiveaway
    {
        return new ChatBoostSourceGiveaway(
            $message['source'],
            $message['giveaway_message_id'],
            isset($message['user']) ? $this->userFactory->create($message['user']) : null,
            $message['prize_star_count'] ?? null,
            $message['is_unclaimed'] ?? null,
        );
    }
}
