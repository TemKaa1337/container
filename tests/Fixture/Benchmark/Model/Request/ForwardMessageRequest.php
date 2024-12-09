<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\ForwardMessageResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<ForwardMessageResponse>
 */
final readonly class ForwardMessageRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public int|string $fromChatId,
        public int $messageId,
        public ?int $messageThreadId = null,
        public ?bool $disableNotification = null,
        public ?bool $protectContent = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::ForwardMessage;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'              => $this->chatId,
                'from_chat_id'         => $this->fromChatId,
                'message_id'           => $this->messageId,
                'message_thread_id'    => $this->messageThreadId,
                'disable_notification' => $this->disableNotification,
                'protect_content'      => $this->protectContent,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
