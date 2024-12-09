<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class ChatShared
{
    /**
     * @param PhotoSize[]|null $photo
     */
    public function __construct(
        public int $requestId,
        public int $chatId,
        public ?string $title = null,
        public ?string $username = null,
        public ?array $photo = null,
    ) {
    }
}
