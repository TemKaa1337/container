<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use DateTimeImmutable;

final readonly class WebhookInfo
{
    /**
     * @param string[]|null $allowedUpdates
     */
    public function __construct(
        public string $url,
        public bool $hasCustomCertificate,
        public int $pendingUpdateCount,
        public ?string $ipAddress = null,
        public ?DateTimeImmutable $lastErrorDate = null,
        public ?string $lastErrorMessage = null,
        public ?DateTimeImmutable $lastSynchronizationErrorDate = null,
        public ?int $maxConnections = null,
        public ?array $allowedUpdates = null,
    ) {
    }
}
