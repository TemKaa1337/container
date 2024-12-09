<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\ForumTopic;

final readonly class ForumTopicFactory
{
    public function create(array $message): ForumTopic
    {
        return new ForumTopic(
            $message['message_thread_id'],
            $message['name'],
            $message['icon_color'],
            $message['icon_custom_emoji_id'] ?? null,
        );
    }
}
