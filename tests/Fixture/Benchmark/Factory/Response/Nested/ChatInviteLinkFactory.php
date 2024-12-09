<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatInviteLink;

final readonly class ChatInviteLinkFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): ChatInviteLink
    {
        return new ChatInviteLink(
            $message['invite_link'],
            $this->userFactory->create($message['creator']),
            $message['creates_join_request'],
            $message['is_primary'],
            $message['is_revoked'],
            $message['name'] ?? null,
            isset($message['expire_date']) ? (new DateTimeImmutable())->setTimestamp(
                $message['expire_date'],
            )->setTimezone(new DateTimeZone('UTC')) : null,
            $message['member_limit'] ?? null,
            $message['pending_join_request_count'] ?? null,
            $message['subscription_period'] ?? null,
            $message['subscription_price'] ?? null,
        );
    }
}
