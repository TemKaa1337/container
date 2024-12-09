<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class UserProfilePhotos
{
    /**
     * @param PhotoSize[][] $photos
     */
    public function __construct(
        public int $totalCount,
        public array $photos,
    ) {
    }
}
