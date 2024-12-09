<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Voice;

final readonly class VoiceFactory
{
    public function create(array $message): Voice
    {
        return new Voice(
            $message['file_id'],
            $message['file_unique_id'],
            $message['duration'],
            $message['mime_type'] ?? null,
            $message['file_size'] ?? null,
        );
    }
}
