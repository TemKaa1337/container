<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;
use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class BusinessConnection
{
    public function __construct(
        public string $id,
        public User $user,
        public int $userChatId,
        public DateTimeImmutable $date,
        public bool $canReply,
        public bool $isEnabled,
    ) {
    }
}
