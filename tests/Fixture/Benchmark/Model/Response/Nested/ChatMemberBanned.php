<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;
use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class ChatMemberBanned
{
    public function __construct(
        public string $status,
        public User $user,
        public DateTimeImmutable $untilDate,
    ) {
    }
}
