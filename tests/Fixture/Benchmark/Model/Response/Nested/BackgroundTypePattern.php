<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class BackgroundTypePattern
{
    public function __construct(
        public string $type,
        public Document $document,
        public BackgroundFillSolid|BackgroundFillGradient|BackgroundFillFreeformGradient $fill,
        public int $intensity,
        public ?true $isInverted = null,
        public ?true $isMoving = null,
    ) {
    }
}
