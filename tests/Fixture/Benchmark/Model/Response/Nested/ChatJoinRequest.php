<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;
use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class ChatJoinRequest
{
    public function __construct(
        public Chat $chat,
        public User $from,
        public int $userChatId,
        public DateTimeImmutable $date,
        public ?string $bio = null,
        public ?ChatInviteLink $inviteLink = null,
    ) {
    }
}
