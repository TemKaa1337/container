<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class SwitchInlineQueryChosenChat
{
    use ArrayFilterTrait;

    public function __construct(
        public ?string $query = null,
        public ?bool $allowUserChats = null,
        public ?bool $allowBotChats = null,
        public ?bool $allowGroupChats = null,
        public ?bool $allowChannelChats = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'query'               => $this->query,
                'allow_user_chats'    => $this->allowUserChats,
                'allow_bot_chats'     => $this->allowBotChats,
                'allow_group_chats'   => $this->allowGroupChats,
                'allow_channel_chats' => $this->allowChannelChats,
            ],
        );
    }
}
