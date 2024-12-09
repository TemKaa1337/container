<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\LogOutResponse;

/**
 * @api
 *
 * @implements RequestInterface<LogOutResponse>
 */
final readonly class LogOutRequest implements RequestInterface
{
    public function __construct()
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::LogOut;
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
