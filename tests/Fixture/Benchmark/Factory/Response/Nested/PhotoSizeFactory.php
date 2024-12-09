<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\PhotoSize;

final readonly class PhotoSizeFactory
{
    public function create(array $message): PhotoSize
    {
        return new PhotoSize(
            $message['file_id'],
            $message['file_unique_id'],
            $message['width'],
            $message['height'],
            $message['file_size'] ?? null,
        );
    }
}
