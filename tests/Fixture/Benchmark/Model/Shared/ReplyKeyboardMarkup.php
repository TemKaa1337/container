<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class ReplyKeyboardMarkup
{
    use ArrayFilterTrait;

    /**
     * @param KeyboardButton[][] $keyboard
     */
    public function __construct(
        public array $keyboard,
        public ?bool $isPersistent = null,
        public ?bool $resizeKeyboard = null,
        public ?bool $oneTimeKeyboard = null,
        public ?string $inputFieldPlaceholder = null,
        public ?bool $selective = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'keyboard'                => array_map(
                    static fn (array $nested): array => array_map(
                        static fn (KeyboardButton $type): array => $type->format(),
                        $nested,
                    ),
                    $this->keyboard,
                ),
                'is_persistent'           => $this->isPersistent,
                'resize_keyboard'         => $this->resizeKeyboard,
                'one_time_keyboard'       => $this->oneTimeKeyboard,
                'input_field_placeholder' => $this->inputFieldPlaceholder,
                'selective'               => $this->selective,
            ],
        );
    }
}
