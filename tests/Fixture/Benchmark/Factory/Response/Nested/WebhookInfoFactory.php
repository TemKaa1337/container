<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Model\Response\Nested\WebhookInfo;

final readonly class WebhookInfoFactory
{
    public function create(array $message): WebhookInfo
    {
        return new WebhookInfo(
            $message['url'],
            $message['has_custom_certificate'],
            $message['pending_update_count'],
            $message['ip_address'] ?? null,
            isset($message['last_error_date']) ? (new DateTimeImmutable())->setTimestamp(
                $message['last_error_date'],
            )->setTimezone(new DateTimeZone('UTC')) : null,
            $message['last_error_message'] ?? null,
            isset($message['last_synchronization_error_date']) ? (new DateTimeImmutable())->setTimestamp(
                $message['last_synchronization_error_date'],
            )->setTimezone(new DateTimeZone('UTC')) : null,
            $message['max_connections'] ?? null,
            $message['allowed_updates'] ?? null,
        );
    }
}
