<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\BotShortDescription;

final readonly class BotShortDescriptionFactory
{
    public function create(array $message): BotShortDescription
    {
        return new BotShortDescription(
            $message['short_description'],
        );
    }
}
