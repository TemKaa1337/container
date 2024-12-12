<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatBoost;

final readonly class ChatBoostFactory
{
    public function __construct(
        private ChatBoostSourcePremiumFactory $chatBoostSourcePremiumFactory,
        private ChatBoostSourceGiftCodeFactory $chatBoostSourceGiftCodeFactory,
        private ChatBoostSourceGiveawayFactory $chatBoostSourceGiveawayFactory,
    ) {
    }

    public function create(array $message): ChatBoost
    {
        return new ChatBoost(
            $message['boost_id'],
            (new DateTimeImmutable())->setTimestamp($message['add_date'])->setTimezone(new DateTimeZone('UTC')),
            (new DateTimeImmutable())->setTimestamp($message['expiration_date'])->setTimezone(new DateTimeZone('UTC')),
            match (true) {
                $message['source']['source'] === 'premium'   => $this->chatBoostSourcePremiumFactory->create(
                    $message['source'],
                ),
                $message['source']['source'] === 'gift_code' => $this->chatBoostSourceGiftCodeFactory->create(
                    $message['source'],
                ),
                $message['source']['source'] === 'giveaway'  => $this->chatBoostSourceGiveawayFactory->create(
                    $message['source'],
                ),
                default                                      => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
        );
    }
}
