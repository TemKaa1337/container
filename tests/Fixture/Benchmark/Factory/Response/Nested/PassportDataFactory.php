<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\EncryptedPassportElement;
use Tests\Fixture\Benchmark\Model\Response\Nested\PassportData;

final readonly class PassportDataFactory
{
    public function __construct(
        private EncryptedPassportElementFactory $encryptedPassportElementFactory,
        private EncryptedCredentialsFactory $encryptedCredentialsFactory,
    ) {
    }

    public function create(array $message): PassportData
    {
        return new PassportData(
            array_map(
                fn (array $nested): EncryptedPassportElement => $this->encryptedPassportElementFactory->create($nested),
                $message['data'],
            ),
            $this->encryptedCredentialsFactory->create($message['credentials']),
        );
    }
}
