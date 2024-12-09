<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\SentWebAppMessage;

final readonly class SentWebAppMessageFactory
{
    public function create(array $message): SentWebAppMessage
    {
        return new SentWebAppMessage(
            $message['inline_message_id'] ?? null,
        );
    }
}
