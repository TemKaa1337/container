<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\RefundStarPaymentResponse;

/**
 * @api
 *
 * @implements RequestInterface<RefundStarPaymentResponse>
 */
final readonly class RefundStarPaymentRequest implements RequestInterface
{
    public function __construct(
        public int $userId,
        public string $telegramPaymentChargeId,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::RefundStarPayment;
    }

    public function getData(): array
    {
        return [
            'user_id'                    => $this->userId,
            'telegram_payment_charge_id' => $this->telegramPaymentChargeId,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
