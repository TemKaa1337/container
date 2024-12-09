<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Model\Response\Nested\Chat;
use Tests\Fixture\Benchmark\Model\Response\Nested\Giveaway;

final readonly class GiveawayFactory
{
    public function __construct(private ChatFactory $chatFactory)
    {
    }

    public function create(array $message): Giveaway
    {
        return new Giveaway(
            array_map(fn (array $nested): Chat => $this->chatFactory->create($nested), $message['chats']),
            (new DateTimeImmutable())->setTimestamp($message['winners_selection_date'])->setTimezone(
                new DateTimeZone('UTC'),
            ),
            $message['winner_count'],
            $message['only_new_members'] ?? null,
            $message['has_public_winners'] ?? null,
            $message['prize_description'] ?? null,
            $message['country_codes'] ?? null,
            $message['prize_star_count'] ?? null,
            $message['premium_subscription_month_count'] ?? null,
        );
    }
}
