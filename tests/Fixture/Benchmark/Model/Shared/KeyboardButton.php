<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class KeyboardButton
{
    use ArrayFilterTrait;

    public function __construct(
        public string $text,
        public KeyboardButtonRequestUsers $requestUsers,
        public KeyboardButtonRequestChat $requestChat,
        public ?bool $requestContact = null,
        public ?bool $requestLocation = null,
        public ?KeyboardButtonPollType $requestPoll = null,
        public ?WebAppInfo $webApp = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'text'             => $this->text,
                'request_users'    => $this->requestUsers->format(),
                'request_chat'     => $this->requestChat->format(),
                'request_contact'  => $this->requestContact,
                'request_location' => $this->requestLocation,
                'request_poll'     => $this->requestPoll?->format() ?: null,
                'web_app'          => $this->webApp?->format() ?: null,
            ],
        );
    }
}
