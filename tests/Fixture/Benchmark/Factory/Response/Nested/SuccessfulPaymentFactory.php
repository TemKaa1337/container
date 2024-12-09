<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Model\Response\Nested\SuccessfulPayment;

final readonly class SuccessfulPaymentFactory
{
    public function __construct(private OrderInfoFactory $orderInfoFactory)
    {
    }

    public function create(array $message): SuccessfulPayment
    {
        return new SuccessfulPayment(
            $message['currency'],
            $message['total_amount'],
            $message['invoice_payload'],
            $message['telegram_payment_charge_id'],
            $message['provider_payment_charge_id'],
            isset($message['subscription_expiration_date']) ? (new DateTimeImmutable())->setTimestamp(
                $message['subscription_expiration_date'],
            )->setTimezone(new DateTimeZone('UTC')) : null,
            $message['is_recurring'] ?? null,
            $message['is_first_recurring'] ?? null,
            $message['shipping_option_id'] ?? null,
            isset($message['order_info']) ? $this->orderInfoFactory->create($message['order_info']) : null,
        );
    }
}
