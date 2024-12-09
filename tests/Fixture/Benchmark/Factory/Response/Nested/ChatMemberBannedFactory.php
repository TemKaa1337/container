<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatMemberBanned;

final readonly class ChatMemberBannedFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): ChatMemberBanned
    {
        return new ChatMemberBanned(
            $message['status'],
            $this->userFactory->create($message['user']),
            (new DateTimeImmutable())->setTimestamp($message['until_date'])->setTimezone(new DateTimeZone('UTC')),
        );
    }
}
