<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class BackgroundTypeWallpaper
{
    public function __construct(
        public string $type,
        public Document $document,
        public int $darkThemeDimming,
        public ?true $isBlurred = null,
        public ?true $isMoving = null,
    ) {
    }
}
