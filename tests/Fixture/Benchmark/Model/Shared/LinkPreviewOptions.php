<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class LinkPreviewOptions
{
    use ArrayFilterTrait;

    public function __construct(
        public ?bool $isDisabled = null,
        public ?string $url = null,
        public ?bool $preferSmallMedia = null,
        public ?bool $preferLargeMedia = null,
        public ?bool $showAboveText = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'is_disabled'        => $this->isDisabled,
                'url'                => $this->url,
                'prefer_small_media' => $this->preferSmallMedia,
                'prefer_large_media' => $this->preferLargeMedia,
                'show_above_text'    => $this->showAboveText,
            ],
        );
    }
}
