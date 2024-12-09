<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\TransactionPartnerAffiliateProgram;

final readonly class TransactionPartnerAffiliateProgramFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): TransactionPartnerAffiliateProgram
    {
        return new TransactionPartnerAffiliateProgram(
            $message['type'],
            $message['commission_per_mille'],
            isset($message['sponsor_user']) ? $this->userFactory->create($message['sponsor_user']) : null,
        );
    }
}
