<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Trait;

trait ArrayFilterTrait
{
    /**
     * @param array<string, mixed> $items
     */
    private function filterNullable(array $items): array
    {
        foreach ($items as $key => $value) {
            if ($value === null) {
                unset($items[$key]);
            }
        }

        return $items;
    }
}
