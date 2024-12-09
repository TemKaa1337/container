<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\InlineQuery;

final readonly class InlineQueryFactory
{
    public function __construct(
        private UserFactory $userFactory,
        private LocationFactory $locationFactory,
    ) {
    }

    public function create(array $message): InlineQuery
    {
        return new InlineQuery(
            $message['id'],
            $this->userFactory->create($message['from']),
            $message['query'],
            $message['offset'],
            $message['chat_type'] ?? null,
            isset($message['location']) ? $this->locationFactory->create($message['location']) : null,
        );
    }
}
