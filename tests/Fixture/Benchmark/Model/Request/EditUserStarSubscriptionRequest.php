<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\EditUserStarSubscriptionResponse;

/**
 * @api
 *
 * @implements RequestInterface<EditUserStarSubscriptionResponse>
 */
final readonly class EditUserStarSubscriptionRequest implements RequestInterface
{
    public function __construct(
        public int $userId,
        public string $telegramPaymentChargeId,
        public bool $isCanceled,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::EditUserStarSubscription;
    }

    public function getData(): array
    {
        return [
            'user_id'                    => $this->userId,
            'telegram_payment_charge_id' => $this->telegramPaymentChargeId,
            'is_canceled'                => $this->isCanceled,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
