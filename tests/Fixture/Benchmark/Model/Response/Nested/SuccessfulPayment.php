<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;

final readonly class SuccessfulPayment
{
    public function __construct(
        public string $currency,
        public int $totalAmount,
        public string $invoicePayload,
        public string $telegramPaymentChargeId,
        public string $providerPaymentChargeId,
        public ?DateTimeImmutable $subscriptionExpirationDate = null,
        public ?true $isRecurring = null,
        public ?true $isFirstRecurring = null,
        public ?string $shippingOptionId = null,
        public ?OrderInfo $orderInfo = null,
    ) {
    }
}
