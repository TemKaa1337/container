<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\EncryptedCredentials;

final readonly class EncryptedCredentialsFactory
{
    public function create(array $message): EncryptedCredentials
    {
        return new EncryptedCredentials(
            $message['data'],
            $message['hash'],
            $message['secret'],
        );
    }
}
