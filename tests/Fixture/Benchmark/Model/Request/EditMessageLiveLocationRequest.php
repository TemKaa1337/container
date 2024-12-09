<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\EditMessageLiveLocationResponse;
use Tests\Fixture\Benchmark\Model\Shared\InlineKeyboardMarkup;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<EditMessageLiveLocationResponse>
 */
final readonly class EditMessageLiveLocationRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public float $latitude,
        public float $longitude,
        public ?string $businessConnectionId = null,
        public int|string|null $chatId = null,
        public ?int $messageId = null,
        public ?string $inlineMessageId = null,
        public ?int $livePeriod = null,
        public ?float $horizontalAccuracy = null,
        public ?int $heading = null,
        public ?int $proximityAlertRadius = null,
        public ?InlineKeyboardMarkup $replyMarkup = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::EditMessageLiveLocation;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'latitude'               => $this->latitude,
                'longitude'              => $this->longitude,
                'business_connection_id' => $this->businessConnectionId,
                'chat_id'                => $this->chatId,
                'message_id'             => $this->messageId,
                'inline_message_id'      => $this->inlineMessageId,
                'live_period'            => $this->livePeriod,
                'horizontal_accuracy'    => $this->horizontalAccuracy,
                'heading'                => $this->heading,
                'proximity_alert_radius' => $this->proximityAlertRadius,
                'reply_markup'           => $this->replyMarkup?->format() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
