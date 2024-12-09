<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetStickerSetTitleResponse;

/**
 * @api
 *
 * @implements RequestInterface<SetStickerSetTitleResponse>
 */
final readonly class SetStickerSetTitleRequest implements RequestInterface
{
    public function __construct(
        public string $name,
        public string $title,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetStickerSetTitle;
    }

    public function getData(): array
    {
        return [
            'name'  => $this->name,
            'title' => $this->title,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
