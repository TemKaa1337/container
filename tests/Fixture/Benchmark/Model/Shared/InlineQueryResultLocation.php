<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineQueryResultLocation
{
    use ArrayFilterTrait;

    public function __construct(
        public string $type,
        public string $id,
        public float $latitude,
        public float $longitude,
        public string $title,
        public ?float $horizontalAccuracy = null,
        public ?int $livePeriod = null,
        public ?int $heading = null,
        public ?int $proximityAlertRadius = null,
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
                'type'                   => $this->type,
                'id'                     => $this->id,
                'latitude'               => $this->latitude,
                'longitude'              => $this->longitude,
                'title'                  => $this->title,
                'horizontal_accuracy'    => $this->horizontalAccuracy,
                'live_period'            => $this->livePeriod,
                'heading'                => $this->heading,
                'proximity_alert_radius' => $this->proximityAlertRadius,
                'reply_markup'           => $this->replyMarkup?->format() ?: null,
                'input_message_content'  => $this->inputMessageContent?->format() ?: null,
                'thumbnail_url'          => $this->thumbnailUrl,
                'thumbnail_width'        => $this->thumbnailWidth,
                'thumbnail_height'       => $this->thumbnailHeight,
            ],
        );
    }
}
