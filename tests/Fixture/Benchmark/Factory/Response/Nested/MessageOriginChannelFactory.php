<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Model\Response\Nested\MessageOriginChannel;

final readonly class MessageOriginChannelFactory
{
    public function __construct(private ChatFactory $chatFactory)
    {
    }

    public function create(array $message): MessageOriginChannel
    {
        return new MessageOriginChannel(
            $message['type'],
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            $this->chatFactory->create($message['chat']),
            $message['message_id'],
            $message['author_signature'] ?? null,
        );
    }
}
