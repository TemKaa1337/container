<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatJoinRequest;

final readonly class ChatJoinRequestFactory
{
    public function __construct(
        private ChatFactory $chatFactory,
        private UserFactory $userFactory,
        private ChatInviteLinkFactory $chatInviteLinkFactory,
    ) {
    }

    public function create(array $message): ChatJoinRequest
    {
        return new ChatJoinRequest(
            $this->chatFactory->create($message['chat']),
            $this->userFactory->create($message['from']),
            $message['user_chat_id'],
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            $message['bio'] ?? null,
            isset($message['invite_link']) ? $this->chatInviteLinkFactory->create($message['invite_link']) : null,
        );
    }
}
