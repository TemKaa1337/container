<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\BusinessMessagesDeleted;

final readonly class BusinessMessagesDeletedFactory
{
    public function __construct(private ChatFactory $chatFactory)
    {
    }

    public function create(array $message): BusinessMessagesDeleted
    {
        return new BusinessMessagesDeleted(
            $message['business_connection_id'],
            $this->chatFactory->create($message['chat']),
            $message['message_ids'],
        );
    }
}
