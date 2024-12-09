<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\ChatBoostUpdated;

final readonly class ChatBoostUpdatedFactory
{
    public function __construct(
        private ChatFactory $chatFactory,
        private ChatBoostFactory $chatBoostFactory,
    ) {
    }

    public function create(array $message): ChatBoostUpdated
    {
        return new ChatBoostUpdated(
            $this->chatFactory->create($message['chat']),
            $this->chatBoostFactory->create($message['boost']),
        );
    }
}
