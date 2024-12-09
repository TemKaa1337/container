<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetStickerMaskPositionResponse;
use Tests\Fixture\Benchmark\Model\Shared\MaskPosition;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetStickerMaskPositionResponse>
 */
final readonly class SetStickerMaskPositionRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public string $sticker,
        public ?MaskPosition $maskPosition = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetStickerMaskPosition;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'sticker'       => $this->sticker,
                'mask_position' => $this->maskPosition?->format() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
