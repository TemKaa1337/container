<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;

final readonly class MessageEntityFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): MessageEntity
    {
        return new MessageEntity(
            $message['type'],
            $message['offset'],
            $message['length'],
            $message['url'] ?? null,
            isset($message['user']) ? $this->userFactory->create($message['user']) : null,
            $message['language'] ?? null,
            $message['custom_emoji_id'] ?? null,
        );
    }
}
