<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class MessageEntity
{
    use ArrayFilterTrait;

    public function __construct(
        public string $type,
        public int $offset,
        public int $length,
        public ?string $url = null,
        public ?User $user = null,
        public ?string $language = null,
        public ?string $customEmojiId = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'type'            => $this->type,
                'offset'          => $this->offset,
                'length'          => $this->length,
                'url'             => $this->url,
                'user'            => $this->user?->format() ?: null,
                'language'        => $this->language,
                'custom_emoji_id' => $this->customEmojiId,
            ],
        );
    }
}
