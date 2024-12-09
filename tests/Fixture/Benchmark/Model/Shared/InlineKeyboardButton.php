<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineKeyboardButton
{
    use ArrayFilterTrait;

    public function __construct(
        public string $text,
        public ?string $url = null,
        public ?string $callbackData = null,
        public ?WebAppInfo $webApp = null,
        public ?LoginUrl $loginUrl = null,
        public ?string $switchInlineQuery = null,
        public ?string $switchInlineQueryCurrentChat = null,
        public ?SwitchInlineQueryChosenChat $switchInlineQueryChosenChat = null,
        public ?CopyTextButton $copyText = null,
        public ?CallbackGame $callbackGame = null,
        public ?bool $pay = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'text'                             => $this->text,
                'url'                              => $this->url,
                'callback_data'                    => $this->callbackData,
                'web_app'                          => $this->webApp?->format() ?: null,
                'login_url'                        => $this->loginUrl?->format() ?: null,
                'switch_inline_query'              => $this->switchInlineQuery,
                'switch_inline_query_current_chat' => $this->switchInlineQueryCurrentChat,
                'switch_inline_query_chosen_chat'  => $this->switchInlineQueryChosenChat?->format() ?: null,
                'copy_text'                        => $this->copyText?->format() ?: null,
                'callback_game'                    => $this->callbackGame?->format() ?: null,
                'pay'                              => $this->pay,
            ],
        );
    }
}
