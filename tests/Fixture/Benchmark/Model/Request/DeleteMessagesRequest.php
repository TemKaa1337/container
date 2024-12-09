<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\DeleteMessagesResponse;

/**
 * @api
 *
 * @implements RequestInterface<DeleteMessagesResponse>
 */
final readonly class DeleteMessagesRequest implements RequestInterface
{
    /**
     * @param int[] $messageIds
     */
    public function __construct(
        public int|string $chatId,
        public array $messageIds,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::DeleteMessages;
    }

    public function getData(): array
    {
        return [
            'chat_id'     => $this->chatId,
            'message_ids' => $this->messageIds,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
