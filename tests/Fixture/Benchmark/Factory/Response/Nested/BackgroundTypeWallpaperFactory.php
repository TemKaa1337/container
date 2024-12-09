<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\BackgroundTypeWallpaper;

final readonly class BackgroundTypeWallpaperFactory
{
    public function __construct(private DocumentFactory $documentFactory)
    {
    }

    public function create(array $message): BackgroundTypeWallpaper
    {
        return new BackgroundTypeWallpaper(
            $message['type'],
            $this->documentFactory->create($message['document']),
            $message['dark_theme_dimming'],
            $message['is_blurred'] ?? null,
            $message['is_moving'] ?? null,
        );
    }
}
