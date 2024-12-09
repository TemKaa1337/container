<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\BotDescription;

final readonly class BotDescriptionFactory
{
    public function create(array $message): BotDescription
    {
        return new BotDescription(
            $message['description'],
        );
    }
}
