<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class EncryptedCredentials
{
    public function __construct(
        public string $data,
        public string $hash,
        public string $secret,
    ) {
    }
}
