<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\PaidMediaPurchased;

final readonly class PaidMediaPurchasedFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): PaidMediaPurchased
    {
        return new PaidMediaPurchased(
            $this->userFactory->create($message['from']),
            $message['paid_media_payload'],
        );
    }
}
