<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

final readonly class WebAppInfo
{
    public function __construct(public string $url)
    {
    }

    public function format(): array
    {
        return [
            'url' => $this->url,
        ];
    }
}
