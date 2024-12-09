<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetChatPhotoResponse;
use Tests\Fixture\Benchmark\Model\Shared\InputFile;

/**
 * @api
 *
 * @implements RequestInterface<SetChatPhotoResponse>
 */
final readonly class SetChatPhotoRequest implements RequestInterface
{
    public function __construct(
        public int|string $chatId,
        public InputFile $photo,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetChatPhoto;
    }

    public function getData(): array
    {
        return [
            'chat_id' => $this->chatId,
            'photo'   => $this->photo->format(),
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
