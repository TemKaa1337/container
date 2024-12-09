<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InputPollOption
{
    use ArrayFilterTrait;

    /**
     * @param MessageEntity[]|null $textEntities
     */
    public function __construct(
        public string $text,
        public ?string $textParseMode = null,
        public ?array $textEntities = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'text'            => $this->text,
                'text_parse_mode' => $this->textParseMode,
                'text_entities'   => $this->textEntities === null
                    ? null
                    : array_map(
                        static fn (MessageEntity $type): array => $type->format(),
                        $this->textEntities,
                    ),
            ],
        );
    }
}
