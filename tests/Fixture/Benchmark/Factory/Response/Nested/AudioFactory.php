<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Audio;

final readonly class AudioFactory
{
    public function __construct(private PhotoSizeFactory $photoSizeFactory)
    {
    }

    public function create(array $message): Audio
    {
        return new Audio(
            $message['file_id'],
            $message['file_unique_id'],
            $message['duration'],
            $message['performer'] ?? null,
            $message['title'] ?? null,
            $message['file_name'] ?? null,
            $message['mime_type'] ?? null,
            $message['file_size'] ?? null,
            isset($message['thumbnail']) ? $this->photoSizeFactory->create($message['thumbnail']) : null,
        );
    }
}
