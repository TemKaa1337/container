<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\DeleteStickerFromSetResponse;

/**
 * @api
 *
 * @implements RequestInterface<DeleteStickerFromSetResponse>
 */
final readonly class DeleteStickerFromSetRequest implements RequestInterface
{
    public function __construct(public string $sticker)
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::DeleteStickerFromSet;
    }

    public function getData(): array
    {
        return [
            'sticker' => $this->sticker,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
