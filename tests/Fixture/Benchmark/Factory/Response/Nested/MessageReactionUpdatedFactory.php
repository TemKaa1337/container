<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Tests\Fixture\Benchmark\Factory\Shared\ReactionTypeCustomEmojiFactory;
use Tests\Fixture\Benchmark\Factory\Shared\ReactionTypeEmojiFactory;
use Tests\Fixture\Benchmark\Factory\Shared\ReactionTypePaidFactory;
use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\MessageReactionUpdated;
use Tests\Fixture\Benchmark\Model\Shared\ReactionTypeCustomEmoji;
use Tests\Fixture\Benchmark\Model\Shared\ReactionTypeEmoji;
use Tests\Fixture\Benchmark\Model\Shared\ReactionTypePaid;

final readonly class MessageReactionUpdatedFactory
{
    public function __construct(
        private ChatFactory $chatFactory,
        private ReactionTypeEmojiFactory $reactionTypeEmojiFactory,
        private ReactionTypeCustomEmojiFactory $reactionTypeCustomEmojiFactory,
        private ReactionTypePaidFactory $reactionTypePaidFactory,
        private UserFactory $userFactory,
    ) {
    }

    public function create(array $message): MessageReactionUpdated
    {
        $factory = match (true) {
            is_array($message['old_reaction']) && $message[0]['type'] === 'emoji' => $this->reactionTypeEmojiFactory,
            is_array(
                $message['old_reaction'],
            ) && $message[0]['type'] === 'custom_emoji'                           => $this->reactionTypeCustomEmojiFactory,
            is_array(
                $message['old_reaction'],
            ) && $message[0]['type'] === 'paid'                                   => $this->reactionTypePaidFactory,
            default                                                               => null,
        };
        $factory = match (true) {
            is_array($message['new_reaction']) && $message[0]['type'] === 'emoji' => $this->reactionTypeEmojiFactory,
            is_array(
                $message['new_reaction'],
            ) && $message[0]['type'] === 'custom_emoji'                           => $this->reactionTypeCustomEmojiFactory,
            is_array(
                $message['new_reaction'],
            ) && $message[0]['type'] === 'paid'                                   => $this->reactionTypePaidFactory,
            default                                                               => null,
        };

        return new MessageReactionUpdated(
            $this->chatFactory->create($message['chat']),
            $message['message_id'],
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            match (true) {
                $factory !== null => array_map(
                    static fn (array $nested,
                    ): ReactionTypeEmoji|ReactionTypeCustomEmoji|ReactionTypePaid => $factory->create($nested),
                    $message['old_reaction'],
                ),
                default           => throw new InvalidArgumentException(
                    sprintf('Could not find factory for message in factory: "%s".', self::class),
                )
            },
            match (true) {
                $factory !== null => array_map(
                    static fn (array $nested,
                    ): ReactionTypeEmoji|ReactionTypeCustomEmoji|ReactionTypePaid => $factory->create($nested),
                    $message['new_reaction'],
                ),
                default           => throw new InvalidArgumentException(
                    sprintf('Could not find factory for message in factory: "%s".', self::class),
                )
            },
            isset($message['user']) ? $this->userFactory->create($message['user']) : null,
            isset($message['actor_chat']) ? $this->chatFactory->create($message['actor_chat']) : null,
        );
    }
}
