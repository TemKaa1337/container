<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Story;

final readonly class StoryFactory
{
    public function __construct(private ChatFactory $chatFactory)
    {
    }

    public function create(array $message): Story
    {
        return new Story(
            $this->chatFactory->create($message['chat']),
            $message['id'],
        );
    }
}
