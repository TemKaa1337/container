<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;
use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class ChatInviteLink
{
    public function __construct(
        public string $inviteLink,
        public User $creator,
        public bool $createsJoinRequest,
        public bool $isPrimary,
        public bool $isRevoked,
        public ?string $name = null,
        public ?DateTimeImmutable $expireDate = null,
        public ?int $memberLimit = null,
        public ?int $pendingJoinRequestCount = null,
        public ?int $subscriptionPeriod = null,
        public ?int $subscriptionPrice = null,
    ) {
    }
}
