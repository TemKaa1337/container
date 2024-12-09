<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Model\Response\Nested\MessageOriginHiddenUser;

final readonly class MessageOriginHiddenUserFactory
{
    public function create(array $message): MessageOriginHiddenUser
    {
        return new MessageOriginHiddenUser(
            $message['type'],
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            $message['sender_user_name'],
        );
    }
}
