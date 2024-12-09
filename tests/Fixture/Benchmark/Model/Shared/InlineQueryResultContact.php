<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineQueryResultContact
{
    use ArrayFilterTrait;

    public function __construct(
        public string $type,
        public string $id,
        public string $phoneNumber,
        public string $firstName,
        public ?string $lastName = null,
        public ?string $vcard = null,
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
                'phone_number'          => $this->phoneNumber,
                'first_name'            => $this->firstName,
                'last_name'             => $this->lastName,
                'vcard'                 => $this->vcard,
                'reply_markup'          => $this->replyMarkup?->format() ?: null,
                'input_message_content' => $this->inputMessageContent?->format() ?: null,
                'thumbnail_url'         => $this->thumbnailUrl,
                'thumbnail_width'       => $this->thumbnailWidth,
                'thumbnail_height'      => $this->thumbnailHeight,
            ],
        );
    }
}
