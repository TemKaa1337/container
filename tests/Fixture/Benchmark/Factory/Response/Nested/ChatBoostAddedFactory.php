<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\ChatBoostAdded;

final readonly class ChatBoostAddedFactory
{
    public function create(array $message): ChatBoostAdded
    {
        return new ChatBoostAdded(
            $message['boost_count'],
        );
    }
}
