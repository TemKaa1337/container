<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineQueryResultVenue
{
    use ArrayFilterTrait;

    public function __construct(
        public string $type,
        public string $id,
        public float $latitude,
        public float $longitude,
        public string $title,
        public string $address,
        public ?string $foursquareId = null,
        public ?string $foursquareType = null,
        public ?string $googlePlaceId = null,
        public ?string $googlePlaceType = null,
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
                'latitude'              => $this->latitude,
                'longitude'             => $this->longitude,
                'title'                 => $this->title,
                'address'               => $this->address,
                'foursquare_id'         => $this->foursquareId,
                'foursquare_type'       => $this->foursquareType,
                'google_place_id'       => $this->googlePlaceId,
                'google_place_type'     => $this->googlePlaceType,
                'reply_markup'          => $this->replyMarkup?->format() ?: null,
                'input_message_content' => $this->inputMessageContent?->format() ?: null,
                'thumbnail_url'         => $this->thumbnailUrl,
                'thumbnail_width'       => $this->thumbnailWidth,
                'thumbnail_height'      => $this->thumbnailHeight,
            ],
        );
    }
}
