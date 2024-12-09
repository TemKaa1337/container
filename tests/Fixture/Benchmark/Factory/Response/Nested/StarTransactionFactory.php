<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Tests\Fixture\Benchmark\Model\Response\Nested\StarTransaction;

final readonly class StarTransactionFactory
{
    public function __construct(
        private TransactionPartnerUserFactory $transactionPartnerUserFactory,
        private TransactionPartnerAffiliateProgramFactory $transactionPartnerAffiliateProgramFactory,
        private TransactionPartnerFragmentFactory $transactionPartnerFragmentFactory,
        private TransactionPartnerTelegramAdsFactory $transactionPartnerTelegramAdsFactory,
        private TransactionPartnerTelegramApiFactory $transactionPartnerTelegramApiFactory,
        private TransactionPartnerOtherFactory $transactionPartnerOtherFactory,
    ) {
    }

    public function create(array $message): StarTransaction
    {
        return new StarTransaction(
            $message['id'],
            $message['amount'],
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            $message['nanostar_amount'] ?? null,
            match (true) {
                !isset($message['source'])                         => null,
                $message['source']['type'] === 'user'              => $this->transactionPartnerUserFactory->create(
                    $message['source'],
                ),
                $message['source']['type'] === 'affiliate_program' => $this->transactionPartnerAffiliateProgramFactory->create(
                    $message['source'],
                ),
                $message['source']['type'] === 'fragment'          => $this->transactionPartnerFragmentFactory->create(
                    $message['source'],
                ),
                $message['source']['type'] === 'telegram_ads'      => $this->transactionPartnerTelegramAdsFactory->create(
                    $message['source'],
                ),
                $message['source']['type'] === 'telegram_api'      => $this->transactionPartnerTelegramApiFactory->create(
                    $message['source'],
                ),
                $message['source']['type'] === 'other'             => $this->transactionPartnerOtherFactory->create(
                    $message['source'],
                ),
                default                                            => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
            match (true) {
                !isset($message['receiver'])                         => null,
                $message['receiver']['type'] === 'user'              => $this->transactionPartnerUserFactory->create(
                    $message['receiver'],
                ),
                $message['receiver']['type'] === 'affiliate_program' => $this->transactionPartnerAffiliateProgramFactory->create(
                    $message['receiver'],
                ),
                $message['receiver']['type'] === 'fragment'          => $this->transactionPartnerFragmentFactory->create(
                    $message['receiver'],
                ),
                $message['receiver']['type'] === 'telegram_ads'      => $this->transactionPartnerTelegramAdsFactory->create(
                    $message['receiver'],
                ),
                $message['receiver']['type'] === 'telegram_api'      => $this->transactionPartnerTelegramApiFactory->create(
                    $message['receiver'],
                ),
                $message['receiver']['type'] === 'other'             => $this->transactionPartnerOtherFactory->create(
                    $message['receiver'],
                ),
                default                                              => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
        );
    }
}
