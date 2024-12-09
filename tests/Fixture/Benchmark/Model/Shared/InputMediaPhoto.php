<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InputMediaPhoto
{
    use ArrayFilterTrait;

    /**
     * @param MessageEntity[]|null $captionEntities
     */
    public function __construct(
        public string $type,
        public string $media,
        public ?string $caption = null,
        public ?string $parseMode = null,
        public ?array $captionEntities = null,
        public ?bool $showCaptionAboveMedia = null,
        public ?bool $hasSpoiler = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'type'                     => $this->type,
                'media'                    => $this->media,
                'caption'                  => $this->caption,
                'parse_mode'               => $this->parseMode,
                'caption_entities'         => $this->captionEntities === null
                    ? null
                    : array_map(
                        static fn (MessageEntity $type): array => $type->format(),
                        $this->captionEntities,
                    ),
                'show_caption_above_media' => $this->showCaptionAboveMedia,
                'has_spoiler'              => $this->hasSpoiler,
            ],
        );
    }
}
