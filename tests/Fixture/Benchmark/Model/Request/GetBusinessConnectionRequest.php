<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\GetBusinessConnectionResponse;

/**
 * @api
 *
 * @implements RequestInterface<GetBusinessConnectionResponse>
 */
final readonly class GetBusinessConnectionRequest implements RequestInterface
{
    public function __construct(public string $businessConnectionId)
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::GetBusinessConnection;
    }

    public function getData(): array
    {
        return [
            'business_connection_id' => $this->businessConnectionId,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
