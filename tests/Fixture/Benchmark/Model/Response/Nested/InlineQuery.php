<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class InlineQuery
{
    public function __construct(
        public string $id,
        public User $from,
        public string $query,
        public string $offset,
        public ?string $chatType = null,
        public ?Location $location = null,
    ) {
    }
}
