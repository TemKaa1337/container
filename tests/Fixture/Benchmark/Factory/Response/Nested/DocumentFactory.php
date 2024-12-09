<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Document;

final readonly class DocumentFactory
{
    public function __construct(private PhotoSizeFactory $photoSizeFactory)
    {
    }

    public function create(array $message): Document
    {
        return new Document(
            $message['file_id'],
            $message['file_unique_id'],
            isset($message['thumbnail']) ? $this->photoSizeFactory->create($message['thumbnail']) : null,
            $message['file_name'] ?? null,
            $message['mime_type'] ?? null,
            $message['file_size'] ?? null,
        );
    }
}
