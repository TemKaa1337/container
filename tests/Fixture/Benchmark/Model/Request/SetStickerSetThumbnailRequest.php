<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetStickerSetThumbnailResponse;
use Tests\Fixture\Benchmark\Model\Shared\InputFile;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetStickerSetThumbnailResponse>
 */
final readonly class SetStickerSetThumbnailRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public string $name,
        public int $userId,
        public string $format,
        public InputFile|string|null $thumbnail = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetStickerSetThumbnail;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'name'      => $this->name,
                'user_id'   => $this->userId,
                'format'    => $this->format,
                'thumbnail' => is_object($this->thumbnail) ? $this->thumbnail->format() : $this->thumbnail,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
