<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InputMediaDocument
{
    use ArrayFilterTrait;

    /**
     * @param MessageEntity[]|null $captionEntities
     */
    public function __construct(
        public string $type,
        public string $media,
        public InputFile|string|null $thumbnail = null,
        public ?string $caption = null,
        public ?string $parseMode = null,
        public ?array $captionEntities = null,
        public ?bool $disableContentTypeDetection = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'type'                           => $this->type,
                'media'                          => $this->media,
                'thumbnail'                      => is_object($this->thumbnail) ? $this->thumbnail->format(
                ) : $this->thumbnail,
                'caption'                        => $this->caption,
                'parse_mode'                     => $this->parseMode,
                'caption_entities'               => $this->captionEntities === null
                    ? null
                    : array_map(
                        static fn (MessageEntity $type): array => $type->format(),
                        $this->captionEntities,
                    ),
                'disable_content_type_detection' => $this->disableContentTypeDetection,
            ],
        );
    }
}
