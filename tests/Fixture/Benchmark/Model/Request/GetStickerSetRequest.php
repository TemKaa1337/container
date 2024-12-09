<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\GetStickerSetResponse;

/**
 * @api
 *
 * @implements RequestInterface<GetStickerSetResponse>
 */
final readonly class GetStickerSetRequest implements RequestInterface
{
    public function __construct(public string $name)
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::GetStickerSet;
    }

    public function getData(): array
    {
        return [
            'name' => $this->name,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
