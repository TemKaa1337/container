<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

final readonly class ShippingOption
{
    /**
     * @param LabeledPrice[] $prices
     */
    public function __construct(
        public string $id,
        public string $title,
        public array $prices,
    ) {
    }

    public function format(): array
    {
        return [
            'id'     => $this->id,
            'title'  => $this->title,
            'prices' => array_map(
                static fn (LabeledPrice $type): array => $type->format(),
                $this->prices,
            ),
        ];
    }
}
