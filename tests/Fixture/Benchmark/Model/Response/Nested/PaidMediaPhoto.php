<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class PaidMediaPhoto
{
    /**
     * @param PhotoSize[] $photo
     */
    public function __construct(
        public string $type,
        public array $photo,
    ) {
    }
}
