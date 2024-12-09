<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;

final readonly class ChatBoostRemoved
{
    public function __construct(
        public Chat $chat,
        public string $boostId,
        public DateTimeImmutable $removeDate,
        public ChatBoostSourcePremium|ChatBoostSourceGiftCode|ChatBoostSourceGiveaway $source,
    ) {
    }
}
