<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\MessageOriginUser;

final readonly class MessageOriginUserFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): MessageOriginUser
    {
        return new MessageOriginUser(
            $message['type'],
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            $this->userFactory->create($message['sender_user']),
        );
    }
}
