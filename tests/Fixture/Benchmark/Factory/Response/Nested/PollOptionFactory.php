<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\MessageEntityFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\PollOption;
use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;

final readonly class PollOptionFactory
{
    public function __construct(private MessageEntityFactory $messageEntityFactory)
    {
    }

    public function create(array $message): PollOption
    {
        return new PollOption(
            $message['text'],
            $message['voter_count'],
            match (true) {
                isset($message['text_entities']) => array_map(
                    fn (array $nested): MessageEntity => $this->messageEntityFactory->create($nested),
                    $message['text_entities'],
                ),
                default                          => null,
            },
        );
    }
}
