<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatMemberAdministrator;

final readonly class ChatMemberAdministratorFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): ChatMemberAdministrator
    {
        return new ChatMemberAdministrator(
            $message['status'],
            $this->userFactory->create($message['user']),
            $message['can_be_edited'],
            $message['is_anonymous'],
            $message['can_manage_chat'],
            $message['can_delete_messages'],
            $message['can_manage_video_chats'],
            $message['can_restrict_members'],
            $message['can_promote_members'],
            $message['can_change_info'],
            $message['can_invite_users'],
            $message['can_post_stories'],
            $message['can_edit_stories'],
            $message['can_delete_stories'],
            $message['can_post_messages'] ?? null,
            $message['can_edit_messages'] ?? null,
            $message['can_pin_messages'] ?? null,
            $message['can_manage_topics'] ?? null,
            $message['custom_title'] ?? null,
        );
    }
}
