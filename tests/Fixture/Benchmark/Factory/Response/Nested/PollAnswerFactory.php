<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\PollAnswer;

final readonly class PollAnswerFactory
{
    public function __construct(
        private ChatFactory $chatFactory,
        private UserFactory $userFactory,
    ) {
    }

    public function create(array $message): PollAnswer
    {
        return new PollAnswer(
            $message['poll_id'],
            $message['option_ids'],
            isset($message['voter_chat']) ? $this->chatFactory->create($message['voter_chat']) : null,
            isset($message['user']) ? $this->userFactory->create($message['user']) : null,
        );
    }
}
