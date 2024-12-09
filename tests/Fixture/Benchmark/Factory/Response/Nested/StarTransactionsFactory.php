<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\StarTransaction;
use Tests\Fixture\Benchmark\Model\Response\Nested\StarTransactions;

final readonly class StarTransactionsFactory
{
    public function __construct(private StarTransactionFactory $starTransactionFactory)
    {
    }

    public function create(array $message): StarTransactions
    {
        return new StarTransactions(
            array_map(fn (array $nested): StarTransaction => $this->starTransactionFactory->create($nested),
                $message['transactions']),
        );
    }
}
