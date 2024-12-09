<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineQueryResultDocument
{
    use ArrayFilterTrait;

    /**
     * @param MessageEntity[]|null $captionEntities
     */
    public function __construct(
        public string $type,
        public string $id,
        public string $title,
        public string $documentUrl,
        public string $mimeType,
        public ?string $caption = null,
        public ?string $parseMode = null,
        public ?array $captionEntities = null,
        public ?string $description = null,
        public ?InlineKeyboardMarkup $replyMarkup = null,
        public InputTextMessageContent|InputLocationMessageContent|InputVenueMessageContent|InputContactMessageContent|InputInvoiceMessageContent|null $inputMessageContent = null,
        public ?string $thumbnailUrl = null,
        public ?int $thumbnailWidth = null,
        public ?int $thumbnailHeight = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'type'                  => $this->type,
                'id'                    => $this->id,
                'title'                 => $this->title,
                'document_url'          => $this->documentUrl,
                'mime_type'             => $this->mimeType,
                'caption'               => $this->caption,
                'parse_mode'            => $this->parseMode,
                'caption_entities'      => $this->captionEntities === null
                    ? null
                    : array_map(
                        static fn (MessageEntity $type): array => $type->format(),
                        $this->captionEntities,
                    ),
                'description'           => $this->description,
                'reply_markup'          => $this->replyMarkup?->format() ?: null,
                'input_message_content' => $this->inputMessageContent?->format() ?: null,
                'thumbnail_url'         => $this->thumbnailUrl,
                'thumbnail_width'       => $this->thumbnailWidth,
                'thumbnail_height'      => $this->thumbnailHeight,
            ],
        );
    }
}
