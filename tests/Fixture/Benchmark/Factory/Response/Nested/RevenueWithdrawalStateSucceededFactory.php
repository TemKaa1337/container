<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Model\Response\Nested\RevenueWithdrawalStateSucceeded;

final readonly class RevenueWithdrawalStateSucceededFactory
{
    public function create(array $message): RevenueWithdrawalStateSucceeded
    {
        return new RevenueWithdrawalStateSucceeded(
            $message['type'],
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            $message['url'],
        );
    }
}
