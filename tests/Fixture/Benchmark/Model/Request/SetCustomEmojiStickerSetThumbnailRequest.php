<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetCustomEmojiStickerSetThumbnailResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetCustomEmojiStickerSetThumbnailResponse>
 */
final readonly class SetCustomEmojiStickerSetThumbnailRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public string $name,
        public ?string $customEmojiId = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetCustomEmojiStickerSetThumbnail;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'name'            => $this->name,
                'custom_emoji_id' => $this->customEmojiId,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
