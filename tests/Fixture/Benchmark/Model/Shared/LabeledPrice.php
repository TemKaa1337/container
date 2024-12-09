<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

final readonly class LabeledPrice
{
    public function __construct(
        public string $label,
        public int $amount,
    ) {
    }

    public function format(): array
    {
        return [
            'label'  => $this->label,
            'amount' => $this->amount,
        ];
    }
}
