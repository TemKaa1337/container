<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Contact;

final readonly class ContactFactory
{
    public function create(array $message): Contact
    {
        return new Contact(
            $message['phone_number'],
            $message['first_name'],
            $message['last_name'] ?? null,
            $message['user_id'] ?? null,
            $message['vcard'] ?? null,
        );
    }
}
