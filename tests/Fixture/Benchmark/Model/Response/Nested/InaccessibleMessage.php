<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;

final readonly class InaccessibleMessage
{
    public function __construct(
        public Chat $chat,
        public int $messageId,
        public DateTimeImmutable $date,
    ) {
    }
}
