<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Model\Response\Nested\PreparedInlineMessage;

final readonly class PreparedInlineMessageFactory
{
    public function create(array $message): PreparedInlineMessage
    {
        return new PreparedInlineMessage(
            $message['id'],
            (new DateTimeImmutable())->setTimestamp($message['expiration_date'])->setTimezone(new DateTimeZone('UTC')),
        );
    }
}
