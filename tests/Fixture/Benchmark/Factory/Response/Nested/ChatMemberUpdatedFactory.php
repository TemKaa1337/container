<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatMemberUpdated;

final readonly class ChatMemberUpdatedFactory
{
    public function __construct(
        private ChatFactory $chatFactory,
        private UserFactory $userFactory,
        private ChatMemberOwnerFactory $chatMemberOwnerFactory,
        private ChatMemberAdministratorFactory $chatMemberAdministratorFactory,
        private ChatMemberMemberFactory $chatMemberMemberFactory,
        private ChatMemberRestrictedFactory $chatMemberRestrictedFactory,
        private ChatMemberLeftFactory $chatMemberLeftFactory,
        private ChatMemberBannedFactory $chatMemberBannedFactory,
        private ChatInviteLinkFactory $chatInviteLinkFactory,
    ) {
    }

    public function create(array $message): ChatMemberUpdated
    {
        return new ChatMemberUpdated(
            $this->chatFactory->create($message['chat']),
            $this->userFactory->create($message['from']),
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            match (true) {
                $message['old_chat_member']['status'] === 'creator'       => $this->chatMemberOwnerFactory->create(
                    $message['old_chat_member'],
                ),
                $message['old_chat_member']['status'] === 'administrator' => $this->chatMemberAdministratorFactory->create(
                    $message['old_chat_member'],
                ),
                $message['old_chat_member']['status'] === 'member'        => $this->chatMemberMemberFactory->create(
                    $message['old_chat_member'],
                ),
                $message['old_chat_member']['status'] === 'restricted'    => $this->chatMemberRestrictedFactory->create(
                    $message['old_chat_member'],
                ),
                $message['old_chat_member']['status'] === 'left'          => $this->chatMemberLeftFactory->create(
                    $message['old_chat_member'],
                ),
                $message['old_chat_member']['status'] === 'kicked'        => $this->chatMemberBannedFactory->create(
                    $message['old_chat_member'],
                ),
                default                                                   => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
            match (true) {
                $message['new_chat_member']['status'] === 'creator'       => $this->chatMemberOwnerFactory->create(
                    $message['new_chat_member'],
                ),
                $message['new_chat_member']['status'] === 'administrator' => $this->chatMemberAdministratorFactory->create(
                    $message['new_chat_member'],
                ),
                $message['new_chat_member']['status'] === 'member'        => $this->chatMemberMemberFactory->create(
                    $message['new_chat_member'],
                ),
                $message['new_chat_member']['status'] === 'restricted'    => $this->chatMemberRestrictedFactory->create(
                    $message['new_chat_member'],
                ),
                $message['new_chat_member']['status'] === 'left'          => $this->chatMemberLeftFactory->create(
                    $message['new_chat_member'],
                ),
                $message['new_chat_member']['status'] === 'kicked'        => $this->chatMemberBannedFactory->create(
                    $message['new_chat_member'],
                ),
                default                                                   => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
            isset($message['invite_link']) ? $this->chatInviteLinkFactory->create($message['invite_link']) : null,
            $message['via_join_request'] ?? null,
            $message['via_chat_folder_invite_link'] ?? null,
        );
    }
}
