<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetStickerEmojiListResponse;

/**
 * @api
 *
 * @implements RequestInterface<SetStickerEmojiListResponse>
 */
final readonly class SetStickerEmojiListRequest implements RequestInterface
{
    /**
     * @param string[] $emojiList
     */
    public function __construct(
        public string $sticker,
        public array $emojiList,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetStickerEmojiList;
    }

    public function getData(): array
    {
        return [
            'sticker'    => $this->sticker,
            'emoji_list' => $this->emojiList,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
