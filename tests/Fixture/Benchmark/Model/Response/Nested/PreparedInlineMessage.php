<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;

final readonly class PreparedInlineMessage
{
    public function __construct(
        public string $id,
        public DateTimeImmutable $expirationDate,
    ) {
    }
}
