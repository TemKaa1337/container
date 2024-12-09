<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\CallbackQuery;

final readonly class CallbackQueryFactory
{
    public function __construct(
        private UserFactory $userFactory,
        private MessageFactory $messageFactory,
        private InaccessibleMessageFactory $inaccessibleMessageFactory,
    ) {
    }

    public function create(array $message): CallbackQuery
    {
        return new CallbackQuery(
            $message['id'],
            $this->userFactory->create($message['from']),
            $message['chat_instance'],
            match (true) {
                !isset($message['message'])           => null,
                $this->hasAnyKey($message['message']) => $this->messageFactory->create($message['message']),
                default                               => $this->inaccessibleMessageFactory->create($message['message'])
            },
            $message['inline_message_id'] ?? null,
            $message['data'] ?? null,
            $message['game_short_name'] ?? null,
        );
    }

    /**
     * @param array<string, mixed> $message
     */
    private function hasAnyKey(array $message): bool
    {
        return !empty(
        array_intersect(array_keys($message), [
            'message_thread_id',
            'from',
            'sender_chat',
            'sender_boost_count',
            'sender_business_bot',
            'business_connection_id',
            'forward_origin',
            'is_topic_message',
            'is_automatic_forward',
            'reply_to_message',
            'external_reply',
            'quote',
            'reply_to_story',
            'via_bot',
            'edit_date',
            'has_protected_content',
            'is_from_offline',
            'media_group_id',
            'author_signature',
            'text',
            'entities',
            'link_preview_options',
            'effect_id',
            'animation',
            'audio',
            'document',
            'paid_media',
            'photo',
            'sticker',
            'story',
            'video',
            'video_note',
            'voice',
            'caption',
            'caption_entities',
            'show_caption_above_media',
            'has_media_spoiler',
            'contact',
            'dice',
            'game',
            'poll',
            'venue',
            'location',
            'new_chat_members',
            'left_chat_member',
            'new_chat_title',
            'new_chat_photo',
            'delete_chat_photo',
            'group_chat_created',
            'supergroup_chat_created',
            'channel_chat_created',
            'message_auto_delete_timer_changed',
            'migrate_to_chat_id',
            'migrate_from_chat_id',
            'pinned_message',
            'invoice',
            'successful_payment',
            'refunded_payment',
            'users_shared',
            'chat_shared',
            'connected_website',
            'write_access_allowed',
            'passport_data',
            'proximity_alert_triggered',
            'boost_added',
            'chat_background_set',
            'forum_topic_created',
            'forum_topic_edited',
            'forum_topic_closed',
            'forum_topic_reopened',
            'general_forum_topic_hidden',
            'general_forum_topic_unhidden',
            'giveaway_created',
            'giveaway',
            'giveaway_winners',
            'giveaway_completed',
            'video_chat_scheduled',
            'video_chat_started',
            'video_chat_ended',
            'video_chat_participants_invited',
            'web_app_data',
            'reply_markup',
        ])
        );
    }
}
