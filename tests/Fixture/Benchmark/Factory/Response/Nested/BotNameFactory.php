<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\BotName;

final readonly class BotNameFactory
{
    public function create(array $message): BotName
    {
        return new BotName(
            $message['name'],
        );
    }
}
