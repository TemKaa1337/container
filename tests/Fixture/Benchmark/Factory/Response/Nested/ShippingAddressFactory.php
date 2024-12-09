<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\ShippingAddress;

final readonly class ShippingAddressFactory
{
    public function create(array $message): ShippingAddress
    {
        return new ShippingAddress(
            $message['country_code'],
            $message['state'],
            $message['city'],
            $message['street_line1'],
            $message['street_line2'],
            $message['post_code'],
        );
    }
}
