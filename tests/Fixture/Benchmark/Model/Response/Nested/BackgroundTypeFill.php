<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class BackgroundTypeFill
{
    public function __construct(
        public string $type,
        public BackgroundFillSolid|BackgroundFillGradient|BackgroundFillFreeformGradient $fill,
        public int $darkThemeDimming,
    ) {
    }
}
