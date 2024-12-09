<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Invoice;

final readonly class InvoiceFactory
{
    public function create(array $message): Invoice
    {
        return new Invoice(
            $message['title'],
            $message['description'],
            $message['start_parameter'],
            $message['currency'],
            $message['total_amount'],
        );
    }
}
