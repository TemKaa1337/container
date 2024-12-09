<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\ReactionTypeCustomEmoji;
use Tests\Fixture\Benchmark\Model\Shared\ReactionTypeEmoji;
use Tests\Fixture\Benchmark\Model\Shared\ReactionTypePaid;

final readonly class ReactionCount
{
    public function __construct(
        public ReactionTypeEmoji|ReactionTypeCustomEmoji|ReactionTypePaid $type,
        public int $totalCount,
    ) {
    }
}
