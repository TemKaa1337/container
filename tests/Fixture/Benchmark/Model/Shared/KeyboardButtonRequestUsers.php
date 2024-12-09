<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class KeyboardButtonRequestUsers
{
    use ArrayFilterTrait;

    public function __construct(
        public int $requestId,
        public ?bool $userIsBot = null,
        public ?bool $userIsPremium = null,
        public ?int $maxQuantity = null,
        public ?bool $requestName = null,
        public ?bool $requestUsername = null,
        public ?bool $requestPhoto = null,
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'request_id'       => $this->requestId,
                'user_is_bot'      => $this->userIsBot,
                'user_is_premium'  => $this->userIsPremium,
                'max_quantity'     => $this->maxQuantity,
                'request_name'     => $this->requestName,
                'request_username' => $this->requestUsername,
                'request_photo'    => $this->requestPhoto,
            ],
        );
    }
}
