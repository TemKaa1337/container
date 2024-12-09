<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class PassportData
{
    /**
     * @param EncryptedPassportElement[] $data
     */
    public function __construct(
        public array $data,
        public EncryptedCredentials $credentials,
    ) {
    }
}
