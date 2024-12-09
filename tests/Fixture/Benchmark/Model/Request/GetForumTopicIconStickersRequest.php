<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\GetForumTopicIconStickersResponse;

/**
 * @api
 *
 * @implements RequestInterface<GetForumTopicIconStickersResponse>
 */
final readonly class GetForumTopicIconStickersRequest implements RequestInterface
{
    public function __construct()
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::GetForumTopicIconStickers;
    }

    public function getData(): array
    {
        return [];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
