<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\CreateChatSubscriptionInviteLinkResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<CreateChatSubscriptionInviteLinkResponse>
 */
final readonly class CreateChatSubscriptionInviteLinkRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public int $subscriptionPeriod,
        public int $subscriptionPrice,
        public ?string $name = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::CreateChatSubscriptionInviteLink;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'             => $this->chatId,
                'subscription_period' => $this->subscriptionPeriod,
                'subscription_price'  => $this->subscriptionPrice,
                'name'                => $this->name,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
