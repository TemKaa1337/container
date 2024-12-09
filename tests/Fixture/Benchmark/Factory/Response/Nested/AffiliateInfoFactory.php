<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\AffiliateInfo;

final readonly class AffiliateInfoFactory
{
    public function __construct(
        private UserFactory $userFactory,
        private ChatFactory $chatFactory,
    ) {
    }

    public function create(array $message): AffiliateInfo
    {
        return new AffiliateInfo(
            $message['commission_per_mille'],
            $message['amount'],
            isset($message['affiliate_user']) ? $this->userFactory->create($message['affiliate_user']) : null,
            isset($message['affiliate_chat']) ? $this->chatFactory->create($message['affiliate_chat']) : null,
            $message['nanostar_amount'] ?? null,
        );
    }
}
