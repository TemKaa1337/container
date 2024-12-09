<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineQueryResultGif
{
    use ArrayFilterTrait;

    /**
     * @param MessageEntity[]|null $captionEntities
     */
    public function __construct(
        public string $type,
        public string $id,
        public string $gifUrl,
        public string $thumbnailUrl,
        public ?int $gifWidth = null,
        public ?int $gifHeight = null,
        public ?int $gifDuration = null,
        public ?string $thumbnailMimeType = null,
        public ?string $title = null,
        public ?string $caption = null,
        public ?string $parseMode = null,
        public ?array $captionEntities = null,
        public ?bool $showCaptionAboveMedia = null,
        public ?InlineKeyboardMarkup $replyMarkup = null,
        public InputTextMessageContent|InputLocationMessageContent|InputVenueMessageContent|InputContactMessageContent|InputInvoiceMessageContent|null $inputMessageContent = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'type'                     => $this->type,
                'id'                       => $this->id,
                'gif_url'                  => $this->gifUrl,
                'thumbnail_url'            => $this->thumbnailUrl,
                'gif_width'                => $this->gifWidth,
                'gif_height'               => $this->gifHeight,
                'gif_duration'             => $this->gifDuration,
                'thumbnail_mime_type'      => $this->thumbnailMimeType,
                'title'                    => $this->title,
                'caption'                  => $this->caption,
                'parse_mode'               => $this->parseMode,
                'caption_entities'         => $this->captionEntities === null
                    ? null
                    : array_map(
                        static fn (MessageEntity $type): array => $type->format(),
                        $this->captionEntities,
                    ),
                'show_caption_above_media' => $this->showCaptionAboveMedia,
                'reply_markup'             => $this->replyMarkup?->format() ?: null,
                'input_message_content'    => $this->inputMessageContent?->format() ?: null,
            ],
        );
    }
}
