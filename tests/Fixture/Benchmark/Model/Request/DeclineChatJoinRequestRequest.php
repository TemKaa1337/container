<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\DeclineChatJoinRequestResponse;

/**
 * @api
 *
 * @implements RequestInterface<DeclineChatJoinRequestResponse>
 */
final readonly class DeclineChatJoinRequestRequest implements RequestInterface
{
    public function __construct(
        public int|string $chatId,
        public int $userId,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::DeclineChatJoinRequest;
    }

    public function getData(): array
    {
        return [
            'chat_id' => $this->chatId,
            'user_id' => $this->userId,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
