<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetChatAdministratorCustomTitleResponse;

/**
 * @api
 *
 * @implements RequestInterface<SetChatAdministratorCustomTitleResponse>
 */
final readonly class SetChatAdministratorCustomTitleRequest implements RequestInterface
{
    public function __construct(
        public int|string $chatId,
        public int $userId,
        public string $customTitle,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetChatAdministratorCustomTitle;
    }

    public function getData(): array
    {
        return [
            'chat_id'      => $this->chatId,
            'user_id'      => $this->userId,
            'custom_title' => $this->customTitle,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
