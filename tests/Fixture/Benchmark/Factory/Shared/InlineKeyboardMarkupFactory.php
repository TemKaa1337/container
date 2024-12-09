<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\InlineKeyboardButton;
use Tests\Fixture\Benchmark\Model\Shared\InlineKeyboardMarkup;

final readonly class InlineKeyboardMarkupFactory
{
    public function __construct(private InlineKeyboardButtonFactory $inlineKeyboardButtonFactory)
    {
    }

    public function create(array $message): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup(
            array_map(
                fn (array $row): array => array_map(
                    fn (array $column): InlineKeyboardButton => $this->inlineKeyboardButtonFactory->create($column),
                    $row,
                ),
                $message['inline_keyboard'],
            ),
        );
    }
}
