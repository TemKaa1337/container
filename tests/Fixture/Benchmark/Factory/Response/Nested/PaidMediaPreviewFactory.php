<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\PaidMediaPreview;

final readonly class PaidMediaPreviewFactory
{
    public function create(array $message): PaidMediaPreview
    {
        return new PaidMediaPreview(
            $message['type'],
            $message['width'] ?? null,
            $message['height'] ?? null,
            $message['duration'] ?? null,
        );
    }
}
