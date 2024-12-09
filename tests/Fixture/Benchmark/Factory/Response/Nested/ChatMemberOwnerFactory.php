<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatMemberOwner;

final readonly class ChatMemberOwnerFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): ChatMemberOwner
    {
        return new ChatMemberOwner(
            $message['status'],
            $this->userFactory->create($message['user']),
            $message['is_anonymous'],
            $message['custom_title'] ?? null,
        );
    }
}
