<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SendPaidMediaResponse;
use Tests\Fixture\Benchmark\Model\Shared\ForceReply;
use Tests\Fixture\Benchmark\Model\Shared\InlineKeyboardMarkup;
use Tests\Fixture\Benchmark\Model\Shared\InputPaidMediaPhoto;
use Tests\Fixture\Benchmark\Model\Shared\InputPaidMediaVideo;
use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;
use Tests\Fixture\Benchmark\Model\Shared\ReplyKeyboardMarkup;
use Tests\Fixture\Benchmark\Model\Shared\ReplyKeyboardRemove;
use Tests\Fixture\Benchmark\Model\Shared\ReplyParameters;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SendPaidMediaResponse>
 */
final readonly class SendPaidMediaRequest implements RequestInterface
{
    use ArrayFilterTrait;

    /**
     * @param InputPaidMediaPhoto[]|InputPaidMediaVideo[] $media
     * @param MessageEntity[]|null                        $captionEntities
     */
    public function __construct(
        public int|string $chatId,
        public int $starCount,
        public array $media,
        public ?string $businessConnectionId = null,
        public ?string $payload = null,
        public ?string $caption = null,
        public ?string $parseMode = null,
        public ?array $captionEntities = null,
        public ?bool $showCaptionAboveMedia = null,
        public ?bool $disableNotification = null,
        public ?bool $protectContent = null,
        public ?bool $allowPaidBroadcast = null,
        public ?ReplyParameters $replyParameters = null,
        public ForceReply|InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|null $replyMarkup = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SendPaidMedia;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'                  => $this->chatId,
                'star_count'               => $this->starCount,
                'media'                    => array_map(
                    static fn (InputPaidMediaPhoto|InputPaidMediaVideo $type): array => $type->format(),
                    $this->media,
                ),
                'business_connection_id'   => $this->businessConnectionId,
                'payload'                  => $this->payload,
                'caption'                  => $this->caption,
                'parse_mode'               => $this->parseMode,
                'caption_entities'         => $this->captionEntities === null
                    ? null
                    : array_map(
                        static fn (MessageEntity $type): array => $type->format(),
                        $this->captionEntities,
                    ),
                'show_caption_above_media' => $this->showCaptionAboveMedia,
                'disable_notification'     => $this->disableNotification,
                'protect_content'          => $this->protectContent,
                'allow_paid_broadcast'     => $this->allowPaidBroadcast,
                'reply_parameters'         => $this->replyParameters?->format() ?: null,
                'reply_markup'             => $this->replyMarkup?->format() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
