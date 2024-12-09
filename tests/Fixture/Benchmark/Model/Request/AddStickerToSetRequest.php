<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\AddStickerToSetResponse;
use Tests\Fixture\Benchmark\Model\Shared\InputSticker;

/**
 * @api
 *
 * @implements RequestInterface<AddStickerToSetResponse>
 */
final readonly class AddStickerToSetRequest implements RequestInterface
{
    public function __construct(
        public int $userId,
        public string $name,
        public InputSticker $sticker,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::AddStickerToSet;
    }

    public function getData(): array
    {
        return [
            'user_id' => $this->userId,
            'name'    => $this->name,
            'sticker' => $this->sticker->format(),
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
