<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\ReplaceStickerInSetResponse;
use Tests\Fixture\Benchmark\Model\Shared\InputSticker;

/**
 * @api
 *
 * @implements RequestInterface<ReplaceStickerInSetResponse>
 */
final readonly class ReplaceStickerInSetRequest implements RequestInterface
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $oldSticker,
        public InputSticker $sticker,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::ReplaceStickerInSet;
    }

    public function getData(): array
    {
        return [
            'user_id'     => $this->userId,
            'name'        => $this->name,
            'old_sticker' => $this->oldSticker,
            'sticker'     => $this->sticker->format(),
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
