<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\ReactionTypeCustomEmoji;

final readonly class ReactionTypeCustomEmojiFactory
{
    public function create(array $message): ReactionTypeCustomEmoji
    {
        return new ReactionTypeCustomEmoji(
            $message['type'],
            $message['custom_emoji_id'],
        );
    }
}
