<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChosenInlineResult;

final readonly class ChosenInlineResultFactory
{
    public function __construct(
        private UserFactory $userFactory,
        private LocationFactory $locationFactory,
    ) {
    }

    public function create(array $message): ChosenInlineResult
    {
        return new ChosenInlineResult(
            $message['result_id'],
            $this->userFactory->create($message['from']),
            $message['query'],
            isset($message['location']) ? $this->locationFactory->create($message['location']) : null,
            $message['inline_message_id'] ?? null,
        );
    }
}
