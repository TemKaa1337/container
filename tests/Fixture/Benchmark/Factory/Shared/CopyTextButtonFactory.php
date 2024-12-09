<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\CopyTextButton;

final readonly class CopyTextButtonFactory
{
    public function create(array $message): CopyTextButton
    {
        return new CopyTextButton(
            $message['text'],
        );
    }
}
