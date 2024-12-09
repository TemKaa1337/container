<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InlineQueryResultGame
{
    use ArrayFilterTrait;

    public function __construct(
        public string $type,
        public string $id,
        public string $gameShortName,
        public ?InlineKeyboardMarkup $replyMarkup = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'type'            => $this->type,
                'id'              => $this->id,
                'game_short_name' => $this->gameShortName,
                'reply_markup'    => $this->replyMarkup?->format() ?: null,
            ],
        );
    }
}
