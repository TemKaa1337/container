<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\DeleteMessageResponse;

/**
 * @api
 *
 * @implements RequestInterface<DeleteMessageResponse>
 */
final readonly class DeleteMessageRequest implements RequestInterface
{
    public function __construct(
        public int|string $chatId,
        public int $messageId,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::DeleteMessage;
    }

    public function getData(): array
    {
        return [
            'chat_id'    => $this->chatId,
            'message_id' => $this->messageId,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
