<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\RevenueWithdrawalStatePending;

final readonly class RevenueWithdrawalStatePendingFactory
{
    public function create(array $message): RevenueWithdrawalStatePending
    {
        return new RevenueWithdrawalStatePending(
            $message['type'],
        );
    }
}
