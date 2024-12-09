<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineQueryResultArticle
{
    use ArrayFilterTrait;

    public function __construct(
        public string $type,
        public string $id,
        public string $title,
        public InputTextMessageContent|InputLocationMessageContent|InputVenueMessageContent|InputContactMessageContent|InputInvoiceMessageContent $inputMessageContent,
        public ?InlineKeyboardMarkup $replyMarkup = null,
        public ?string $url = null,
        public ?bool $hideUrl = null,
        public ?string $description = null,
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
                'input_message_content' => $this->inputMessageContent->format(),
                'reply_markup'          => $this->replyMarkup?->format() ?: null,
                'url'                   => $this->url,
                'hide_url'              => $this->hideUrl,
                'description'           => $this->description,
                'thumbnail_url'         => $this->thumbnailUrl,
                'thumbnail_width'       => $this->thumbnailWidth,
                'thumbnail_height'      => $this->thumbnailHeight,
            ],
        );
    }
}
