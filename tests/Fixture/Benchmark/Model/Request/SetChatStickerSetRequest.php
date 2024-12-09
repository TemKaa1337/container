<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetChatStickerSetResponse;

/**
 * @api
 *
 * @implements RequestInterface<SetChatStickerSetResponse>
 */
final readonly class SetChatStickerSetRequest implements RequestInterface
{
    public function __construct(
        public int|string $chatId,
        public string $stickerSetName,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetChatStickerSet;
    }

    public function getData(): array
    {
        return [
            'chat_id'          => $this->chatId,
            'sticker_set_name' => $this->stickerSetName,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
