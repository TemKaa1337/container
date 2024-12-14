<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response;

use Tests\Fixture\Benchmark\Model\Response\Nested\ResponseParameters;
use Tests\Fixture\Benchmark\Model\Response\Nested\UserProfilePhotos;
use Tests\Fixture\Benchmark\Model\ResponseInterface;

final readonly class GetUserProfilePhotosResponse implements ResponseInterface
{
    public function __construct(
        public bool $ok,
        public ?UserProfilePhotos $result = null,
        public ?string $description = null,
        public ?int $errorCode = null,
        public ?ResponseParameters $parameters = null,
    ) {
    }
}