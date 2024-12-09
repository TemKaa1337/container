<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineQueryResultsButton
{
    use ArrayFilterTrait;

    public function __construct(
        public string $text,
        public ?WebAppInfo $webApp = null,
        public ?string $startParameter = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'text'            => $this->text,
                'web_app'         => $this->webApp?->format() ?: null,
                'start_parameter' => $this->startParameter,
            ],
        );
    }
}
