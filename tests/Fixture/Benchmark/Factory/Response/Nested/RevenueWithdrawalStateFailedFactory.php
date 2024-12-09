<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\RevenueWithdrawalStateFailed;

final readonly class RevenueWithdrawalStateFailedFactory
{
    public function create(array $message): RevenueWithdrawalStateFailed
    {
        return new RevenueWithdrawalStateFailed(
            $message['type'],
        );
    }
}
