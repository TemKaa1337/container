<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\VideoChatParticipantsInvited;
use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class VideoChatParticipantsInvitedFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): VideoChatParticipantsInvited
    {
        return new VideoChatParticipantsInvited(
            array_map(fn (array $nested): User => $this->userFactory->create($nested), $message['users']),
        );
    }
}
