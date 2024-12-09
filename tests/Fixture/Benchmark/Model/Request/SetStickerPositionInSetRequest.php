<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetStickerPositionInSetResponse;

/**
 * @api
 *
 * @implements RequestInterface<SetStickerPositionInSetResponse>
 */
final readonly class SetStickerPositionInSetRequest implements RequestInterface
{
    public function __construct(
        public string $sticker,
        public int $position,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetStickerPositionInSet;
    }

    public function getData(): array
    {
        return [
            'sticker'  => $this->sticker,
            'position' => $this->position,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
