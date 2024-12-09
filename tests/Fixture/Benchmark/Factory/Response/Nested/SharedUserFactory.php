<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\PhotoSize;
use Tests\Fixture\Benchmark\Model\Response\Nested\SharedUser;

final readonly class SharedUserFactory
{
    public function __construct(private PhotoSizeFactory $photoSizeFactory)
    {
    }

    public function create(array $message): SharedUser
    {
        return new SharedUser(
            $message['user_id'],
            $message['first_name'] ?? null,
            $message['last_name'] ?? null,
            $message['username'] ?? null,
            match (true) {
                isset($message['photo']) => array_map(
                    fn (array $nested): PhotoSize => $this->photoSizeFactory->create($nested),
                    $message['photo'],
                ),
                default                  => null,
            },
        );
    }
}
