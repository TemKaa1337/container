<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class ForumTopic
{
    public function __construct(
        public int $messageThreadId,
        public string $name,
        public int $iconColor,
        public ?string $iconCustomEmojiId = null,
    ) {
    }
}
