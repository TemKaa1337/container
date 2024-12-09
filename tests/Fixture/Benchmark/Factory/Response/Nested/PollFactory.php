<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Factory\Shared\MessageEntityFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\Poll;
use Tests\Fixture\Benchmark\Model\Response\Nested\PollOption;
use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;

final readonly class PollFactory
{
    public function __construct(
        private PollOptionFactory $pollOptionFactory,
        private MessageEntityFactory $messageEntityFactory,
    ) {
    }

    public function create(array $message): Poll
    {
        return new Poll(
            $message['id'],
            $message['question'],
            array_map(fn (array $nested): PollOption => $this->pollOptionFactory->create($nested), $message['options']),
            $message['total_voter_count'],
            $message['is_closed'],
            $message['is_anonymous'],
            $message['type'],
            $message['allows_multiple_answers'],
            match (true) {
                isset($message['question_entities']) => array_map(
                    fn (array $nested): MessageEntity => $this->messageEntityFactory->create($nested),
                    $message['question_entities'],
                ),
                default                              => null,
            },
            $message['correct_option_id'] ?? null,
            $message['explanation'] ?? null,
            match (true) {
                isset($message['explanation_entities']) => array_map(
                    fn (array $nested): MessageEntity => $this->messageEntityFactory->create($nested),
                    $message['explanation_entities'],
                ),
                default                                 => null,
            },
            $message['open_period'] ?? null,
            isset($message['close_date']) ? (new DateTimeImmutable())->setTimestamp(
                $message['close_date'],
            )->setTimezone(new DateTimeZone('UTC')) : null,
        );
    }
}
