<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ShippingQuery;

final readonly class ShippingQueryFactory
{
    public function __construct(
        private UserFactory $userFactory,
        private ShippingAddressFactory $shippingAddressFactory,
    ) {
    }

    public function create(array $message): ShippingQuery
    {
        return new ShippingQuery(
            $message['id'],
            $this->userFactory->create($message['from']),
            $message['invoice_payload'],
            $this->shippingAddressFactory->create($message['shipping_address']),
        );
    }
}
