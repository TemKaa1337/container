<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Model\Response\Nested\MessageReactionCountUpdated;
use Tests\Fixture\Benchmark\Model\Response\Nested\ReactionCount;

final readonly class MessageReactionCountUpdatedFactory
{
    public function __construct(
        private ChatFactory $chatFactory,
        private ReactionCountFactory $reactionCountFactory,
    ) {
    }

    public function create(array $message): MessageReactionCountUpdated
    {
        return new MessageReactionCountUpdated(
            $this->chatFactory->create($message['chat']),
            $message['message_id'],
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            array_map(fn (array $nested): ReactionCount => $this->reactionCountFactory->create($nested),
                $message['reactions']),
        );
    }
}
