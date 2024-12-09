<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Model\Response\Nested\VideoChatScheduled;

final readonly class VideoChatScheduledFactory
{
    public function create(array $message): VideoChatScheduled
    {
        return new VideoChatScheduled(
            (new DateTimeImmutable())->setTimestamp($message['start_date'])->setTimezone(new DateTimeZone('UTC')),
        );
    }
}
