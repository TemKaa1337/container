<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;
use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;

final readonly class Poll
{
    /**
     * @param PollOption[]         $options
     * @param MessageEntity[]|null $questionEntities
     * @param MessageEntity[]|null $explanationEntities
     */
    public function __construct(
        public string $id,
        public string $question,
        public array $options,
        public int $totalVoterCount,
        public bool $isClosed,
        public bool $isAnonymous,
        public string $type,
        public bool $allowsMultipleAnswers,
        public ?array $questionEntities = null,
        public ?int $correctOptionId = null,
        public ?string $explanation = null,
        public ?array $explanationEntities = null,
        public ?int $openPeriod = null,
        public ?DateTimeImmutable $closeDate = null,
    ) {
    }
}
