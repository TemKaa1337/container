<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\ChatPermissions;

final readonly class ChatPermissionsFactory
{
    public function create(array $message): ChatPermissions
    {
        return new ChatPermissions(
            $message['can_send_messages'] ?? null,
            $message['can_send_audios'] ?? null,
            $message['can_send_documents'] ?? null,
            $message['can_send_photos'] ?? null,
            $message['can_send_videos'] ?? null,
            $message['can_send_video_notes'] ?? null,
            $message['can_send_voice_notes'] ?? null,
            $message['can_send_polls'] ?? null,
            $message['can_send_other_messages'] ?? null,
            $message['can_add_web_page_previews'] ?? null,
            $message['can_change_info'] ?? null,
            $message['can_invite_users'] ?? null,
            $message['can_pin_messages'] ?? null,
            $message['can_manage_topics'] ?? null,
        );
    }
}
