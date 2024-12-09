<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\SharedUser;
use Tests\Fixture\Benchmark\Model\Response\Nested\UsersShared;

final readonly class UsersSharedFactory
{
    public function __construct(private SharedUserFactory $sharedUserFactory)
    {
    }

    public function create(array $message): UsersShared
    {
        return new UsersShared(
            $message['request_id'],
            array_map(fn (array $nested): SharedUser => $this->sharedUserFactory->create($nested), $message['users']),
        );
    }
}
