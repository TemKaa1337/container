<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineQueryResultPhoto
{
    use ArrayFilterTrait;

    /**
     * @param MessageEntity[]|null $captionEntities
     */
    public function __construct(
        public string $type,
        public string $id,
        public string $photoUrl,
        public string $thumbnailUrl,
        public ?int $photoWidth = null,
        public ?int $photoHeight = null,
        public ?string $title = null,
        public ?string $description = null,
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
                'photo_url'                => $this->photoUrl,
                'thumbnail_url'            => $this->thumbnailUrl,
                'photo_width'              => $this->photoWidth,
                'photo_height'             => $this->photoHeight,
                'title'                    => $this->title,
                'description'              => $this->description,
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
