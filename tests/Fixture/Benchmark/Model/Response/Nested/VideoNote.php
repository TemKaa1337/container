<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class VideoNote
{
    public function __construct(
        public string $fileId,
        public string $fileUniqueId,
        public int $length,
        public int $duration,
        public ?PhotoSize $thumbnail = null,
        public ?int $fileSize = null,
    ) {
    }
}
