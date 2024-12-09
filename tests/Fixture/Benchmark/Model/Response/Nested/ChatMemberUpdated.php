<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;
use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class ChatMemberUpdated
{
    public function __construct(
        public Chat $chat,
        public User $from,
        public DateTimeImmutable $date,
        public ChatMemberOwner|ChatMemberAdministrator|ChatMemberMember|ChatMemberRestricted|ChatMemberLeft|ChatMemberBanned $oldChatMember,
        public ChatMemberOwner|ChatMemberAdministrator|ChatMemberMember|ChatMemberRestricted|ChatMemberLeft|ChatMemberBanned $newChatMember,
        public ?ChatInviteLink $inviteLink = null,
        public ?bool $viaJoinRequest = null,
        public ?bool $viaChatFolderInviteLink = null,
    ) {
    }
}
