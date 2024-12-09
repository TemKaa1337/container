<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class PollAnswer
{
    /**
     * @param int[] $optionIds
     */
    public function __construct(
        public string $pollId,
        public array $optionIds,
        public ?Chat $voterChat = null,
        public ?User $user = null,
    ) {
    }
}
