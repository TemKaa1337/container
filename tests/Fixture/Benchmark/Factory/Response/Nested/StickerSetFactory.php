<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Sticker;
use Tests\Fixture\Benchmark\Model\Response\Nested\StickerSet;

final readonly class StickerSetFactory
{
    public function __construct(
        private StickerFactory $stickerFactory,
        private PhotoSizeFactory $photoSizeFactory,
    ) {
    }

    public function create(array $message): StickerSet
    {
        return new StickerSet(
            $message['name'],
            $message['title'],
            $message['sticker_type'],
            array_map(fn (array $nested): Sticker => $this->stickerFactory->create($nested), $message['stickers']),
            isset($message['thumbnail']) ? $this->photoSizeFactory->create($message['thumbnail']) : null,
        );
    }
}
