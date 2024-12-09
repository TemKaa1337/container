<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatMemberRestricted;

final readonly class ChatMemberRestrictedFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): ChatMemberRestricted
    {
        return new ChatMemberRestricted(
            $message['status'],
            $this->userFactory->create($message['user']),
            $message['is_member'],
            $message['can_send_messages'],
            $message['can_send_audios'],
            $message['can_send_documents'],
            $message['can_send_photos'],
            $message['can_send_videos'],
            $message['can_send_video_notes'],
            $message['can_send_voice_notes'],
            $message['can_send_polls'],
            $message['can_send_other_messages'],
            $message['can_add_web_page_previews'],
            $message['can_change_info'],
            $message['can_invite_users'],
            $message['can_pin_messages'],
            $message['can_manage_topics'],
            (new DateTimeImmutable())->setTimestamp($message['until_date'])->setTimezone(new DateTimeZone('UTC')),
        );
    }
}
