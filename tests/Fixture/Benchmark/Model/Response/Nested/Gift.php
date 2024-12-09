<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class Gift
{
    public function __construct(
        public string $id,
        public Sticker $sticker,
        public int $starCount,
        public ?int $totalCount = null,
        public ?int $remainingCount = null,
    ) {
    }
}
