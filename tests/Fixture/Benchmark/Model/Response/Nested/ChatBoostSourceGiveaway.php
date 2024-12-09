<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class ChatBoostSourceGiveaway
{
    public function __construct(
        public string $source,
        public int $giveawayMessageId,
        public ?User $user = null,
        public ?int $prizeStarCount = null,
        public ?true $isUnclaimed = null,
    ) {
    }
}
