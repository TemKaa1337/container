<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class Contact
{
    public function __construct(
        public string $phoneNumber,
        public string $firstName,
        public ?string $lastName = null,
        public ?int $userId = null,
        public ?string $vcard = null,
    ) {
    }
}
