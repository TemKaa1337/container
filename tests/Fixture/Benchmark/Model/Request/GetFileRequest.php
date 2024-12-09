<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\GetFileResponse;

/**
 * @api
 *
 * @implements RequestInterface<GetFileResponse>
 */
final readonly class GetFileRequest implements RequestInterface
{
    public function __construct(public string $fileId)
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::GetFile;
    }

    public function getData(): array
    {
        return [
            'file_id' => $this->fileId,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
