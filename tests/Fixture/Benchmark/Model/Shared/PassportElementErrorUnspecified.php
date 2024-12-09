<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

final readonly class PassportElementErrorUnspecified
{
    public function __construct(
        public string $source,
        public string $type,
        public string $elementHash,
        public string $message,
    ) {
    }

    public function format(): array
    {
        return [
            'source'       => $this->source,
            'type'         => $this->type,
            'element_hash' => $this->elementHash,
            'message'      => $this->message,
        ];
    }
}
