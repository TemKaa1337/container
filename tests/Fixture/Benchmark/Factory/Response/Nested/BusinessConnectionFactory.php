<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\BusinessConnection;

final readonly class BusinessConnectionFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): BusinessConnection
    {
        return new BusinessConnection(
            $message['id'],
            $this->userFactory->create($message['user']),
            $message['user_chat_id'],
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            $message['can_reply'],
            $message['is_enabled'],
        );
    }
}
