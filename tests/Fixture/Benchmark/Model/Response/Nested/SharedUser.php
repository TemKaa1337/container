<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class SharedUser
{
    /**
     * @param PhotoSize[]|null $photo
     */
    public function __construct(
        public int $userId,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $username = null,
        public ?array $photo = null,
    ) {
    }
}
