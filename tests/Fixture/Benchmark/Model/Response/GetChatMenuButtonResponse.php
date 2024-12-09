<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response;

use Tests\Fixture\Benchmark\Model\Response\Nested\ResponseParameters;
use Tests\Fixture\Benchmark\Model\ResponseInterface;
use Tests\Fixture\Benchmark\Model\Shared\MenuButtonCommands;
use Tests\Fixture\Benchmark\Model\Shared\MenuButtonDefault;
use Tests\Fixture\Benchmark\Model\Shared\MenuButtonWebApp;

final readonly class GetChatMenuButtonResponse implements ResponseInterface
{
    public function __construct(
        public bool $ok,
        public MenuButtonCommands|MenuButtonWebApp|MenuButtonDefault|null $result = null,
        public ?string $description = null,
        public ?int $errorCode = null,
        public ?ResponseParameters $parameters = null,
    ) {
    }
}
