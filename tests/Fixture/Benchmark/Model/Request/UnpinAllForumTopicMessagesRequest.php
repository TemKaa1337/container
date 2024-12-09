<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\UnpinAllForumTopicMessagesResponse;

/**
 * @api
 *
 * @implements RequestInterface<UnpinAllForumTopicMessagesResponse>
 */
final readonly class UnpinAllForumTopicMessagesRequest implements RequestInterface
{
    public function __construct(
        public int|string $chatId,
        public int $messageThreadId,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::UnpinAllForumTopicMessages;
    }

    public function getData(): array
    {
        return [
            'chat_id'           => $this->chatId,
            'message_thread_id' => $this->messageThreadId,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
