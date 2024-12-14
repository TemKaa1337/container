<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\BotCommand;

final readonly class BotCommandFactory
{
    public function create(array $message): BotCommand
    {
        return new BotCommand(
            $message['command'],
            $message['description'],
        );
    }
}