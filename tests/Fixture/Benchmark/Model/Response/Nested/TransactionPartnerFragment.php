<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class TransactionPartnerFragment
{
    public function __construct(
        public string $type,
        public RevenueWithdrawalStatePending|RevenueWithdrawalStateSucceeded|RevenueWithdrawalStateFailed|null $withdrawalState = null,
    ) {
    }
}
