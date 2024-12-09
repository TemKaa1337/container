<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\TransactionPartnerTelegramAds;

final readonly class TransactionPartnerTelegramAdsFactory
{
    public function create(array $message): TransactionPartnerTelegramAds
    {
        return new TransactionPartnerTelegramAds(
            $message['type'],
        );
    }
}
