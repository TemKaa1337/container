<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\ForumTopicEdited;

final readonly class ForumTopicEditedFactory
{
    public function create(array $message): ForumTopicEdited
    {
        return new ForumTopicEdited(
            $message['name'] ?? null,
            $message['icon_custom_emoji_id'] ?? null,
        );
    }
}
