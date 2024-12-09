<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InputContactMessageContent
{
    use ArrayFilterTrait;

    public function __construct(
        public string $phoneNumber,
        public string $firstName,
        public ?string $lastName = null,
        public ?string $vcard = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'phone_number' => $this->phoneNumber,
                'first_name'   => $this->firstName,
                'last_name'    => $this->lastName,
                'vcard'        => $this->vcard,
            ],
        );
    }
}
