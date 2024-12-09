<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\UnpinChatMessageResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<UnpinChatMessageResponse>
 */
final readonly class UnpinChatMessageRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public ?string $businessConnectionId = null,
        public ?int $messageId = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::UnpinChatMessage;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'                => $this->chatId,
                'business_connection_id' => $this->businessConnectionId,
                'message_id'             => $this->messageId,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
