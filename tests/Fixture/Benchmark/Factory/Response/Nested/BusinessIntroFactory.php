<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\BusinessIntro;

final readonly class BusinessIntroFactory
{
    public function __construct(private StickerFactory $stickerFactory)
    {
    }

    public function create(array $message): BusinessIntro
    {
        return new BusinessIntro(
            $message['title'] ?? null,
            $message['message'] ?? null,
            isset($message['sticker']) ? $this->stickerFactory->create($message['sticker']) : null,
        );
    }
}
