<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\File;

final readonly class FileFactory
{
    public function create(array $message): File
    {
        return new File(
            $message['file_id'],
            $message['file_unique_id'],
            $message['file_size'] ?? null,
            $message['file_path'] ?? null,
        );
    }
}
