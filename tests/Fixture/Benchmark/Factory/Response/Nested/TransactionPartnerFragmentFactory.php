<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use InvalidArgumentException;
use Tests\Fixture\Benchmark\Model\Response\Nested\TransactionPartnerFragment;

final readonly class TransactionPartnerFragmentFactory
{
    public function __construct(
        private RevenueWithdrawalStatePendingFactory $revenueWithdrawalStatePendingFactory,
        private RevenueWithdrawalStateSucceededFactory $revenueWithdrawalStateSucceededFactory,
        private RevenueWithdrawalStateFailedFactory $revenueWithdrawalStateFailedFactory,
    ) {
    }

    public function create(array $message): TransactionPartnerFragment
    {
        return new TransactionPartnerFragment(
            $message['type'],
            match (true) {
                !isset($message['withdrawal_state'])                 => null,
                $message['withdrawal_state']['type'] === 'pending'   => $this->revenueWithdrawalStatePendingFactory->create(
                    $message['withdrawal_state'],
                ),
                $message['withdrawal_state']['type'] === 'succeeded' => $this->revenueWithdrawalStateSucceededFactory->create(
                    $message['withdrawal_state'],
                ),
                $message['withdrawal_state']['type'] === 'failed'    => $this->revenueWithdrawalStateFailedFactory->create(
                    $message['withdrawal_state'],
                ),
                default                                              => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
        );
    }
}
