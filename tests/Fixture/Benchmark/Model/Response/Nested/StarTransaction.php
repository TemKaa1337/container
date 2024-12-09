<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;

final readonly class StarTransaction
{
    public function __construct(
        public string $id,
        public int $amount,
        public DateTimeImmutable $date,
        public ?int $nanostarAmount = null,
        public TransactionPartnerUser|TransactionPartnerAffiliateProgram|TransactionPartnerFragment|TransactionPartnerTelegramAds|TransactionPartnerTelegramApi|TransactionPartnerOther|null $source = null,
        public TransactionPartnerUser|TransactionPartnerAffiliateProgram|TransactionPartnerFragment|TransactionPartnerTelegramAds|TransactionPartnerTelegramApi|TransactionPartnerOther|null $receiver = null,
    ) {
    }
}
