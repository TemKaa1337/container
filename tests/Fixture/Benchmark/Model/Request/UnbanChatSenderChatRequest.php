<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\UnbanChatSenderChatResponse;

/**
 * @api
 *
 * @implements RequestInterface<UnbanChatSenderChatResponse>
 */
final readonly class UnbanChatSenderChatRequest implements RequestInterface
{
    public function __construct(
        public int|string $chatId,
        public int $senderChatId,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::UnbanChatSenderChat;
    }

    public function getData(): array
    {
        return [
            'chat_id'        => $this->chatId,
            'sender_chat_id' => $this->senderChatId,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
