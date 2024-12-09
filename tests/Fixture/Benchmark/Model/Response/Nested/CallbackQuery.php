<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class CallbackQuery
{
    public function __construct(
        public string $id,
        public User $from,
        public string $chatInstance,
        public Message|InaccessibleMessage|null $message = null,
        public ?string $inlineMessageId = null,
        public ?string $data = null,
        public ?string $gameShortName = null,
    ) {
    }
}
