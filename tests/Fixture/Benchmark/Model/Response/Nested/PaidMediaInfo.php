<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class PaidMediaInfo
{
    /**
     * @param PaidMediaPreview[]|PaidMediaPhoto[]|PaidMediaVideo[] $paidMedia
     */
    public function __construct(
        public int $starCount,
        public array $paidMedia,
    ) {
    }
}
