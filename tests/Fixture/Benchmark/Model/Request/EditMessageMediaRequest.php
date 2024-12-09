<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\EditMessageMediaResponse;
use Tests\Fixture\Benchmark\Model\Shared\InlineKeyboardMarkup;
use Tests\Fixture\Benchmark\Model\Shared\InputMediaAnimation;
use Tests\Fixture\Benchmark\Model\Shared\InputMediaAudio;
use Tests\Fixture\Benchmark\Model\Shared\InputMediaDocument;
use Tests\Fixture\Benchmark\Model\Shared\InputMediaPhoto;
use Tests\Fixture\Benchmark\Model\Shared\InputMediaVideo;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<EditMessageMediaResponse>
 */
final readonly class EditMessageMediaRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public InputMediaAnimation|InputMediaDocument|InputMediaAudio|InputMediaPhoto|InputMediaVideo $media,
        public ?string $businessConnectionId = null,
        public int|string|null $chatId = null,
        public ?int $messageId = null,
        public ?string $inlineMessageId = null,
        public ?InlineKeyboardMarkup $replyMarkup = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::EditMessageMedia;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'media'                  => $this->media->format(),
                'business_connection_id' => $this->businessConnectionId,
                'chat_id'                => $this->chatId,
                'message_id'             => $this->messageId,
                'inline_message_id'      => $this->inlineMessageId,
                'reply_markup'           => $this->replyMarkup?->format() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
