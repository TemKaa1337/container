<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\MenuButtonCommands;

final readonly class MenuButtonCommandsFactory
{
    public function create(array $message): MenuButtonCommands
    {
        return new MenuButtonCommands(
            $message['type'],
        );
    }
}
