<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\GameHighScore;

final readonly class GameHighScoreFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    public function create(array $message): GameHighScore
    {
        return new GameHighScore(
            $message['position'],
            $this->userFactory->create($message['user']),
            $message['score'],
        );
    }
}
