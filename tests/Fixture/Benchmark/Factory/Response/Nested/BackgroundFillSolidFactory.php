<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\BackgroundFillSolid;

final readonly class BackgroundFillSolidFactory
{
    public function create(array $message): BackgroundFillSolid
    {
        return new BackgroundFillSolid(
            $message['type'],
            $message['color'],
        );
    }
}
