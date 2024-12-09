<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Gift;
use Tests\Fixture\Benchmark\Model\Response\Nested\Gifts;

final readonly class GiftsFactory
{
    public function __construct(private GiftFactory $giftFactory)
    {
    }

    public function create(array $message): Gifts
    {
        return new Gifts(
            array_map(fn (array $nested): Gift => $this->giftFactory->create($nested), $message['gifts']),
        );
    }
}
