<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;

final readonly class MessageOriginHiddenUser
{
    public function __construct(
        public string $type,
        public DateTimeImmutable $date,
        public string $senderUserName,
    ) {
    }
}
