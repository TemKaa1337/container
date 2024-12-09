<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;

final readonly class Game
{
    /**
     * @param PhotoSize[]          $photo
     * @param MessageEntity[]|null $textEntities
     */
    public function __construct(
        public string $title,
        public string $description,
        public array $photo,
        public ?string $text = null,
        public ?array $textEntities = null,
        public ?Animation $animation = null,
    ) {
    }
}
