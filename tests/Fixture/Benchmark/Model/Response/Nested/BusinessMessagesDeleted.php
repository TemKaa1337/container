<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class BusinessMessagesDeleted
{
    /**
     * @param int[] $messageIds
     */
    public function __construct(
        public string $businessConnectionId,
        public Chat $chat,
        public array $messageIds,
    ) {
    }
}
