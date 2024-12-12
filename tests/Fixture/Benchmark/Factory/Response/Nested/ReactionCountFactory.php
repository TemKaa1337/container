<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use InvalidArgumentException;
use Tests\Fixture\Benchmark\Factory\Shared\ReactionTypeCustomEmojiFactory;
use Tests\Fixture\Benchmark\Factory\Shared\ReactionTypeEmojiFactory;
use Tests\Fixture\Benchmark\Factory\Shared\ReactionTypePaidFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ReactionCount;

final readonly class ReactionCountFactory
{
    public function __construct(
        private ReactionTypeEmojiFactory $reactionTypeEmojiFactory,
        private ReactionTypeCustomEmojiFactory $reactionTypeCustomEmojiFactory,
        private ReactionTypePaidFactory $reactionTypePaidFactory,
    ) {
    }

    public function create(array $message): ReactionCount
    {
        return new ReactionCount(
            match (true) {
                $message['type']['type'] === 'emoji'        => $this->reactionTypeEmojiFactory->create(
                    $message['type'],
                ),
                $message['type']['type'] === 'custom_emoji' => $this->reactionTypeCustomEmojiFactory->create(
                    $message['type'],
                ),
                $message['type']['type'] === 'paid'         => $this->reactionTypePaidFactory->create($message['type']),
                default                                     => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
            $message['total_count'],
        );
    }
}
