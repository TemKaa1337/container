<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\TransactionPartnerOther;

final readonly class TransactionPartnerOtherFactory
{
    public function create(array $message): TransactionPartnerOther
    {
        return new TransactionPartnerOther(
            $message['type'],
        );
    }
}
