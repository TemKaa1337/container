<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Model\Response\Nested\MessageOriginChat;

final readonly class MessageOriginChatFactory
{
    public function __construct(private ChatFactory $chatFactory)
    {
    }

    public function create(array $message): MessageOriginChat
    {
        return new MessageOriginChat(
            $message['type'],
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            $this->chatFactory->create($message['sender_chat']),
            $message['author_signature'] ?? null,
        );
    }
}
