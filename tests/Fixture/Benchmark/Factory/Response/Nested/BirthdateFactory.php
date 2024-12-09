<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Birthdate;

final readonly class BirthdateFactory
{
    public function create(array $message): Birthdate
    {
        return new Birthdate(
            $message['day'],
            $message['month'],
            $message['year'] ?? null,
        );
    }
}
