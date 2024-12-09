<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Gift;

final readonly class GiftFactory
{
    public function __construct(private StickerFactory $stickerFactory)
    {
    }

    public function create(array $message): Gift
    {
        return new Gift(
            $message['id'],
            $this->stickerFactory->create($message['sticker']),
            $message['star_count'],
            $message['total_count'] ?? null,
            $message['remaining_count'] ?? null,
        );
    }
}
