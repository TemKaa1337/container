<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineQueryResultMpeg4Gif
{
    use ArrayFilterTrait;

    /**
     * @param MessageEntity[]|null $captionEntities
     */
    public function __construct(
        public string $type,
        public string $id,
        public string $mpeg4Url,
        public string $thumbnailUrl,
        public ?int $mpeg4Width = null,
        public ?int $mpeg4Height = null,
        public ?int $mpeg4Duration = null,
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
                'mpeg4_url'                => $this->mpeg4Url,
                'thumbnail_url'            => $this->thumbnailUrl,
                'mpeg4_width'              => $this->mpeg4Width,
                'mpeg4_height'             => $this->mpeg4Height,
                'mpeg4_duration'           => $this->mpeg4Duration,
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
