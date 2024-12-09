<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class LoginUrl
{
    use ArrayFilterTrait;

    public function __construct(
        public string $url,
        public ?string $forwardText = null,
        public ?string $botUsername = null,
        public ?bool $requestWriteAccess = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'url'                  => $this->url,
                'forward_text'         => $this->forwardText,
                'bot_username'         => $this->botUsername,
                'request_write_access' => $this->requestWriteAccess,
            ],
        );
    }
}
