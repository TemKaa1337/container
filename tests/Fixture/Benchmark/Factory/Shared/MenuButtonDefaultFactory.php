<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\MenuButtonDefault;

final readonly class MenuButtonDefaultFactory
{
    public function create(array $message): MenuButtonDefault
    {
        return new MenuButtonDefault(
            $message['type'],
        );
    }
}
