<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class TransactionPartnerAffiliateProgram
{
    public function __construct(
        public string $type,
        public int $commissionPerMille,
        public ?User $sponsorUser = null,
    ) {
    }
}
