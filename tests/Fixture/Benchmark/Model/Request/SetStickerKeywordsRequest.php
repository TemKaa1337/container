<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetStickerKeywordsResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetStickerKeywordsResponse>
 */
final readonly class SetStickerKeywordsRequest implements RequestInterface
{
    use ArrayFilterTrait;

    /**
     * @param string[]|null $keywords
     */
    public function __construct(
        public string $sticker,
        public ?array $keywords = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetStickerKeywords;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'sticker'  => $this->sticker,
                'keywords' => $this->keywords,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
