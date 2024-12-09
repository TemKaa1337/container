<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\VideoNote;

final readonly class VideoNoteFactory
{
    public function __construct(private PhotoSizeFactory $photoSizeFactory)
    {
    }

    public function create(array $message): VideoNote
    {
        return new VideoNote(
            $message['file_id'],
            $message['file_unique_id'],
            $message['length'],
            $message['duration'],
            isset($message['thumbnail']) ? $this->photoSizeFactory->create($message['thumbnail']) : null,
            $message['file_size'] ?? null,
        );
    }
}
