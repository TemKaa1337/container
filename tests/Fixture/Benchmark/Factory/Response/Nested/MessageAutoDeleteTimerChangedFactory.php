<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\MessageAutoDeleteTimerChanged;

final readonly class MessageAutoDeleteTimerChangedFactory
{
    public function create(array $message): MessageAutoDeleteTimerChanged
    {
        return new MessageAutoDeleteTimerChanged(
            $message['message_auto_delete_time'],
        );
    }
}
