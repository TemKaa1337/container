<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class AffiliateInfo
{
    public function __construct(
        public int $commissionPerMille,
        public int $amount,
        public ?User $affiliateUser = null,
        public ?Chat $affiliateChat = null,
        public ?int $nanostarAmount = null,
    ) {
    }
}
