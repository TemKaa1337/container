<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;

final readonly class ChatBoost
{
    public function __construct(
        public string $boostId,
        public DateTimeImmutable $addDate,
        public DateTimeImmutable $expirationDate,
        public ChatBoostSourcePremium|ChatBoostSourceGiftCode|ChatBoostSourceGiveaway $source,
    ) {
    }
}
