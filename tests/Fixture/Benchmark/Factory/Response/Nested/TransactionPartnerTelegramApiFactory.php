<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\TransactionPartnerTelegramApi;

final readonly class TransactionPartnerTelegramApiFactory
{
    public function create(array $message): TransactionPartnerTelegramApi
    {
        return new TransactionPartnerTelegramApi(
            $message['type'],
            $message['request_count'],
        );
    }
}
