<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Tests\Fixture\Benchmark\Factory\Shared\ChatPermissionsFactory;
use Tests\Fixture\Benchmark\Factory\Shared\ReactionTypeCustomEmojiFactory;
use Tests\Fixture\Benchmark\Factory\Shared\ReactionTypeEmojiFactory;
use Tests\Fixture\Benchmark\Factory\Shared\ReactionTypePaidFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatFullInfo;
use Tests\Fixture\Benchmark\Model\Shared\ReactionTypeCustomEmoji;
use Tests\Fixture\Benchmark\Model\Shared\ReactionTypeEmoji;
use Tests\Fixture\Benchmark\Model\Shared\ReactionTypePaid;

final readonly class ChatFullInfoFactory
{
    public function __construct(
        private ChatPhotoFactory $chatPhotoFactory,
        private BirthdateFactory $birthdateFactory,
        private BusinessIntroFactory $businessIntroFactory,
        private BusinessLocationFactory $businessLocationFactory,
        private BusinessOpeningHoursFactory $businessOpeningHoursFactory,
        private ChatFactory $chatFactory,
        private ReactionTypeEmojiFactory $reactionTypeEmojiFactory,
        private ReactionTypeCustomEmojiFactory $reactionTypeCustomEmojiFactory,
        private ReactionTypePaidFactory $reactionTypePaidFactory,
        private MessageFactory $messageFactory,
        private ChatPermissionsFactory $chatPermissionsFactory,
        private ChatLocationFactory $chatLocationFactory,
    ) {
    }

    public function create(array $message): ChatFullInfo
    {
        $factory = match (true) {
            !isset($message['available_reactions']) => null,
            is_array(
                $message['available_reactions'],
            ) && $message[0]['type'] === 'emoji' => $this->reactionTypeEmojiFactory,
            is_array(
                $message['available_reactions'],
            ) && $message[0]['type'] === 'custom_emoji' => $this->reactionTypeCustomEmojiFactory,
            is_array(
                $message['available_reactions'],
            ) && $message[0]['type'] === 'paid' => $this->reactionTypePaidFactory,
            default => null,
        };

        return new ChatFullInfo(
            $message['id'],
            $message['type'],
            $message['accent_color_id'],
            $message['max_reaction_count'],
            $message['title'] ?? null,
            $message['username'] ?? null,
            $message['first_name'] ?? null,
            $message['last_name'] ?? null,
            $message['is_forum'] ?? null,
            isset($message['photo']) ? $this->chatPhotoFactory->create($message['photo']) : null,
            $message['active_usernames'] ?? null,
            isset($message['birthdate']) ? $this->birthdateFactory->create($message['birthdate']) : null,
            isset($message['business_intro']) ? $this->businessIntroFactory->create($message['business_intro']) : null,
            isset($message['business_location']) ? $this->businessLocationFactory->create(
                $message['business_location'],
            ) : null,
            isset($message['business_opening_hours']) ? $this->businessOpeningHoursFactory->create(
                $message['business_opening_hours'],
            ) : null,
            isset($message['personal_chat']) ? $this->chatFactory->create($message['personal_chat']) : null,
            match (true) {
                !isset($message['available_reactions']) => null,
                $factory !== null                       => array_map(
                    static fn (array $nested,
                    ): ReactionTypeEmoji|ReactionTypeCustomEmoji|ReactionTypePaid => $factory->create($nested),
                    $message['available_reactions'],
                ),
                default                                 => throw new InvalidArgumentException(
                    sprintf('Could not find factory for message in factory: "%s".', self::class),
                )
            },
            $message['background_custom_emoji_id'] ?? null,
            $message['profile_accent_color_id'] ?? null,
            $message['profile_background_custom_emoji_id'] ?? null,
            $message['emoji_status_custom_emoji_id'] ?? null,
            isset($message['emoji_status_expiration_date']) ? (new DateTimeImmutable())->setTimestamp(
                $message['emoji_status_expiration_date'],
            )->setTimezone(new DateTimeZone('UTC')) : null,
            $message['bio'] ?? null,
            $message['has_private_forwards'] ?? null,
            $message['has_restricted_voice_and_video_messages'] ?? null,
            $message['join_to_send_messages'] ?? null,
            $message['join_by_request'] ?? null,
            $message['description'] ?? null,
            $message['invite_link'] ?? null,
            isset($message['pinned_message']) ? $this->messageFactory->create($message['pinned_message']) : null,
            isset($message['permissions']) ? $this->chatPermissionsFactory->create($message['permissions']) : null,
            $message['can_send_paid_media'] ?? null,
            $message['slow_mode_delay'] ?? null,
            $message['unrestrict_boost_count'] ?? null,
            $message['message_auto_delete_time'] ?? null,
            $message['has_aggressive_anti_spam_enabled'] ?? null,
            $message['has_hidden_members'] ?? null,
            $message['has_protected_content'] ?? null,
            $message['has_visible_history'] ?? null,
            $message['sticker_set_name'] ?? null,
            $message['can_set_sticker_set'] ?? null,
            $message['custom_emoji_sticker_set_name'] ?? null,
            $message['linked_chat_id'] ?? null,
            isset($message['location']) ? $this->chatLocationFactory->create($message['location']) : null,
        );
    }
}
