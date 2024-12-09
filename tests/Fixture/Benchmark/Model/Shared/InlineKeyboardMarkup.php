<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

final readonly class InlineKeyboardMarkup
{
    /**
     * @param InlineKeyboardButton[][] $inlineKeyboard
     */
    public function __construct(public array $inlineKeyboard)
    {
    }

    public function format(): array
    {
        return [
            'inline_keyboard' => array_map(
                static fn (array $nested): array => array_map(
                    static fn (InlineKeyboardButton $type): array => $type->format(),
                    $nested,
                ),
                $this->inlineKeyboard,
            ),
        ];
    }
}
