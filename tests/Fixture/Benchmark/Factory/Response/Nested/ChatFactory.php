<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Chat;

final readonly class ChatFactory
{
    public function create(array $message): Chat
    {
        return new Chat(
            $message['id'],
            $message['type'],
            $message['title'] ?? null,
            $message['username'] ?? null,
            $message['first_name'] ?? null,
            $message['last_name'] ?? null,
            $message['is_forum'] ?? null,
        );
    }
}
