<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Video;

final readonly class VideoFactory
{
    public function __construct(private PhotoSizeFactory $photoSizeFactory)
    {
    }

    public function create(array $message): Video
    {
        return new Video(
            $message['file_id'],
            $message['file_unique_id'],
            $message['width'],
            $message['height'],
            $message['duration'],
            isset($message['thumbnail']) ? $this->photoSizeFactory->create($message['thumbnail']) : null,
            $message['file_name'] ?? null,
            $message['mime_type'] ?? null,
            $message['file_size'] ?? null,
        );
    }
}