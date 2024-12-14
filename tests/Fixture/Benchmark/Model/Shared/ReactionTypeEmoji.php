<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

final readonly class ReactionTypeEmoji
{
    public function __construct(
        public string $type,
        public string $emoji,
    ) {
    }

    public function format(): array
    {
        return [
            'type'  => $this->type,
            'emoji' => $this->emoji,
        ];
    }
}