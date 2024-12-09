<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\UploadStickerFileResponse;
use Tests\Fixture\Benchmark\Model\Shared\InputFile;

/**
 * @api
 *
 * @implements RequestInterface<UploadStickerFileResponse>
 */
final readonly class UploadStickerFileRequest implements RequestInterface
{
    public function __construct(
        public int $userId,
        public InputFile $sticker,
        public string $stickerFormat,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::UploadStickerFile;
    }

    public function getData(): array
    {
        return [
            'user_id'        => $this->userId,
            'sticker'        => $this->sticker->format(),
            'sticker_format' => $this->stickerFormat,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
