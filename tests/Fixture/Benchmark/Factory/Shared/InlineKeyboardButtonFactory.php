<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\InlineKeyboardButton;

final readonly class InlineKeyboardButtonFactory
{
    public function __construct(
        private WebAppInfoFactory $webAppInfoFactory,
        private LoginUrlFactory $loginUrlFactory,
        private SwitchInlineQueryChosenChatFactory $switchInlineQueryChosenChatFactory,
        private CopyTextButtonFactory $copyTextButtonFactory,
        private CallbackGameFactory $callbackGameFactory,
    ) {
    }

    public function create(array $message): InlineKeyboardButton
    {
        return new InlineKeyboardButton(
            $message['text'],
            $message['url'] ?? null,
            $message['callback_data'] ?? null,
            isset($message['web_app']) ? $this->webAppInfoFactory->create($message['web_app']) : null,
            isset($message['login_url']) ? $this->loginUrlFactory->create($message['login_url']) : null,
            $message['switch_inline_query'] ?? null,
            $message['switch_inline_query_current_chat'] ?? null,
            isset($message['switch_inline_query_chosen_chat']) ? $this->switchInlineQueryChosenChatFactory->create(
                $message['switch_inline_query_chosen_chat'],
            ) : null,
            isset($message['copy_text']) ? $this->copyTextButtonFactory->create($message['copy_text']) : null,
            isset($message['callback_game']) ? $this->callbackGameFactory->create($message['callback_game']) : null,
            $message['pay'] ?? null,
        );
    }
}
