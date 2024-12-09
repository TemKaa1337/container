<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatBoostSourcePremium;

final readonly class ChatBoostSourcePremiumFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): ChatBoostSourcePremium
    {
        return new ChatBoostSourcePremium(
            $message['source'],
            $this->userFactory->create($message['user']),
        );
    }
}
