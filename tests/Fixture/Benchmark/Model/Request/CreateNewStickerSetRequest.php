<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\CreateNewStickerSetResponse;
use Tests\Fixture\Benchmark\Model\Shared\InputSticker;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<CreateNewStickerSetResponse>
 */
final readonly class CreateNewStickerSetRequest implements RequestInterface
{
    use ArrayFilterTrait;

    /**
     * @param InputSticker[] $stickers
     */
    public function __construct(
        public int $userId,
        public string $name,
        public string $title,
        public array $stickers,
        public ?string $stickerType = null,
        public ?bool $needsRepainting = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::CreateNewStickerSet;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'user_id'          => $this->userId,
                'name'             => $this->name,
                'title'            => $this->title,
                'stickers'         => array_map(
                    static fn (InputSticker $type): array => $type->format(),
                    $this->stickers,
                ),
                'sticker_type'     => $this->stickerType,
                'needs_repainting' => $this->needsRepainting,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
