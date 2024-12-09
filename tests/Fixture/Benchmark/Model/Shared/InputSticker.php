<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InputSticker
{
    use ArrayFilterTrait;

    /**
     * @param string[]      $emojiList
     * @param string[]|null $keywords
     */
    public function __construct(
        public InputFile|string $sticker,
        public string $format,
        public array $emojiList,
        public ?MaskPosition $maskPosition = null,
        public ?array $keywords = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'sticker'       => is_object($this->sticker) ? $this->sticker->format() : $this->sticker,
                'format'        => $this->format,
                'emoji_list'    => $this->emojiList,
                'mask_position' => $this->maskPosition?->format() ?: null,
                'keywords'      => $this->keywords,
            ],
        );
    }
}
