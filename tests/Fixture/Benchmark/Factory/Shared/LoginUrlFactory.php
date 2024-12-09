<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\LoginUrl;

final readonly class LoginUrlFactory
{
    public function create(array $message): LoginUrl
    {
        return new LoginUrl(
            $message['url'],
            $message['forward_text'] ?? null,
            $message['bot_username'] ?? null,
            $message['request_write_access'] ?? null,
        );
    }
}
