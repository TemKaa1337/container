<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;

final readonly class Giveaway
{
    /**
     * @param Chat[]        $chats
     * @param string[]|null $countryCodes
     */
    public function __construct(
        public array $chats,
        public DateTimeImmutable $winnersSelectionDate,
        public int $winnerCount,
        public ?true $onlyNewMembers = null,
        public ?true $hasPublicWinners = null,
        public ?string $prizeDescription = null,
        public ?array $countryCodes = null,
        public ?int $prizeStarCount = null,
        public ?int $premiumSubscriptionMonthCount = null,
    ) {
    }
}
