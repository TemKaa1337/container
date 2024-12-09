<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineQueryResultCachedVoice
{
    use ArrayFilterTrait;

    /**
     * @param MessageEntity[]|null $captionEntities
     */
    public function __construct(
        public string $type,
        public string $id,
        public string $voiceFileId,
        public string $title,
        public ?string $caption = null,
        public ?string $parseMode = null,
        public ?array $captionEntities = null,
        public ?InlineKeyboardMarkup $replyMarkup = null,
        public InputTextMessageContent|InputLocationMessageContent|InputVenueMessageContent|InputContactMessageContent|InputInvoiceMessageContent|null $inputMessageContent = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'type'                  => $this->type,
                'id'                    => $this->id,
                'voice_file_id'         => $this->voiceFileId,
                'title'                 => $this->title,
                'caption'               => $this->caption,
                'parse_mode'            => $this->parseMode,
                'caption_entities'      => $this->captionEntities === null
                    ? null
                    : array_map(
                        static fn (MessageEntity $type): array => $type->format(),
                        $this->captionEntities,
                    ),
                'reply_markup'          => $this->replyMarkup?->format() ?: null,
                'input_message_content' => $this->inputMessageContent?->format() ?: null,
            ],
        );
    }
}
