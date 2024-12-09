<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\MessageId;

final readonly class MessageIdFactory
{
    public function create(array $message): MessageId
    {
        return new MessageId(
            $message['message_id'],
        );
    }
}
