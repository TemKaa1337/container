<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\ReactionTypePaid;

final readonly class ReactionTypePaidFactory
{
    public function create(array $message): ReactionTypePaid
    {
        return new ReactionTypePaid(
            $message['type'],
        );
    }
}
