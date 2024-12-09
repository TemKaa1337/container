<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Dice;

final readonly class DiceFactory
{
    public function create(array $message): Dice
    {
        return new Dice(
            $message['emoji'],
            $message['value'],
        );
    }
}
