<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;

final readonly class PollOption
{
    /**
     * @param MessageEntity[]|null $textEntities
     */
    public function __construct(
        public string $text,
        public int $voterCount,
        public ?array $textEntities = null,
    ) {
    }
}
