<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InputTextMessageContent
{
    use ArrayFilterTrait;

    /**
     * @param MessageEntity[]|null $entities
     */
    public function __construct(
        public string $messageText,
        public ?string $parseMode = null,
        public ?array $entities = null,
        public ?LinkPreviewOptions $linkPreviewOptions = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'message_text'         => $this->messageText,
                'parse_mode'           => $this->parseMode,
                'entities'             => $this->entities === null
                    ? null
                    : array_map(
                        static fn (MessageEntity $type): array => $type->format(),
                        $this->entities,
                    ),
                'link_preview_options' => $this->linkPreviewOptions?->format() ?: null,
            ],
        );
    }
}
