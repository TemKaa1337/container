<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\EditMessageTextResponse;
use Tests\Fixture\Benchmark\Model\Shared\InlineKeyboardMarkup;
use Tests\Fixture\Benchmark\Model\Shared\LinkPreviewOptions;
use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<EditMessageTextResponse>
 */
final readonly class EditMessageTextRequest implements RequestInterface
{
    use ArrayFilterTrait;

    /**
     * @param MessageEntity[]|null $entities
     */
    public function __construct(
        public string $text,
        public ?string $businessConnectionId = null,
        public int|string|null $chatId = null,
        public ?int $messageId = null,
        public ?string $inlineMessageId = null,
        public ?string $parseMode = null,
        public ?array $entities = null,
        public ?LinkPreviewOptions $linkPreviewOptions = null,
        public ?InlineKeyboardMarkup $replyMarkup = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::EditMessageText;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'text'                   => $this->text,
                'business_connection_id' => $this->businessConnectionId,
                'chat_id'                => $this->chatId,
                'message_id'             => $this->messageId,
                'inline_message_id'      => $this->inlineMessageId,
                'parse_mode'             => $this->parseMode,
                'entities'               => $this->entities === null
                    ? null
                    : array_map(
                        static fn (MessageEntity $type): array => $type->format(),
                        $this->entities,
                    ),
                'link_preview_options'   => $this->linkPreviewOptions?->format() ?: null,
                'reply_markup'           => $this->replyMarkup?->format() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
