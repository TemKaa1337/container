<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InputPaidMediaVideo
{
    use ArrayFilterTrait;

    public function __construct(
        public string $type,
        public string $media,
        public InputFile|string|null $thumbnail = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?int $duration = null,
        public ?bool $supportsStreaming = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'type'               => $this->type,
                'media'              => $this->media,
                'thumbnail'          => is_object($this->thumbnail) ? $this->thumbnail->format() : $this->thumbnail,
                'width'              => $this->width,
                'height'             => $this->height,
                'duration'           => $this->duration,
                'supports_streaming' => $this->supportsStreaming,
            ],
        );
    }
}
