<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class ChatMemberOwner
{
    public function __construct(
        public string $status,
        public User $user,
        public bool $isAnonymous,
        public ?string $customTitle = null,
    ) {
    }
}
