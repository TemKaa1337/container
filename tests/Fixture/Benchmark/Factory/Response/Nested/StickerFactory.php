<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\MaskPositionFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\Sticker;

final readonly class StickerFactory
{
    public function __construct(
        private PhotoSizeFactory $photoSizeFactory,
        private FileFactory $fileFactory,
        private MaskPositionFactory $maskPositionFactory,
    ) {
    }

    public function create(array $message): Sticker
    {
        return new Sticker(
            $message['file_id'],
            $message['file_unique_id'],
            $message['type'],
            $message['width'],
            $message['height'],
            $message['is_animated'],
            $message['is_video'],
            isset($message['thumbnail']) ? $this->photoSizeFactory->create($message['thumbnail']) : null,
            $message['emoji'] ?? null,
            $message['set_name'] ?? null,
            isset($message['premium_animation']) ? $this->fileFactory->create($message['premium_animation']) : null,
            isset($message['mask_position']) ? $this->maskPositionFactory->create($message['mask_position']) : null,
            $message['custom_emoji_id'] ?? null,
            $message['needs_repainting'] ?? null,
            $message['file_size'] ?? null,
        );
    }
}
