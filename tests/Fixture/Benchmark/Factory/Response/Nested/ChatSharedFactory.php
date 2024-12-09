<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\ChatShared;
use Tests\Fixture\Benchmark\Model\Response\Nested\PhotoSize;

final readonly class ChatSharedFactory
{
    public function __construct(private PhotoSizeFactory $photoSizeFactory)
    {
    }

    public function create(array $message): ChatShared
    {
        return new ChatShared(
            $message['request_id'],
            $message['chat_id'],
            $message['title'] ?? null,
            $message['username'] ?? null,
            match (true) {
                isset($message['photo']) => array_map(
                    fn (array $nested): PhotoSize => $this->photoSizeFactory->create($nested),
                    $message['photo'],
                ),
                default                  => null,
            },
        );
    }
}
