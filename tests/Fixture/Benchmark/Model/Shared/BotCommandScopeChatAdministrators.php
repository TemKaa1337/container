<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

final readonly class BotCommandScopeChatAdministrators
{
    public function __construct(
        public string $type,
        public int|string $chatId,
    ) {
    }

    public function format(): array
    {
        return [
            'type'    => $this->type,
            'chat_id' => $this->chatId,
        ];
    }
}
