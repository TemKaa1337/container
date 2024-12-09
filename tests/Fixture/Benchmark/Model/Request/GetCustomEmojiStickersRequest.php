<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\GetCustomEmojiStickersResponse;

/**
 * @api
 *
 * @implements RequestInterface<GetCustomEmojiStickersResponse>
 */
final readonly class GetCustomEmojiStickersRequest implements RequestInterface
{
    /**
     * @param string[] $customEmojiIds
     */
    public function __construct(public array $customEmojiIds)
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::GetCustomEmojiStickers;
    }

    public function getData(): array
    {
        return [
            'custom_emoji_ids' => $this->customEmojiIds,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
