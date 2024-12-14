<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\HideGeneralForumTopicResponse;

/**
 * @api
 *
 * @implements RequestInterface<HideGeneralForumTopicResponse>
 */
final readonly class HideGeneralForumTopicRequest implements RequestInterface
{
    public function __construct(public int|string $chatId)
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::HideGeneralForumTopic;
    }

    public function getData(): array
    {
        return [
            'chat_id' => $this->chatId,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}