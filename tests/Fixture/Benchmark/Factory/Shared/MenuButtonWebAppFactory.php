<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\MenuButtonWebApp;

final readonly class MenuButtonWebAppFactory
{
    public function __construct(private WebAppInfoFactory $webAppInfoFactory)
    {
    }

    public function create(array $message): MenuButtonWebApp
    {
        return new MenuButtonWebApp(
            $message['type'],
            $message['text'],
            $this->webAppInfoFactory->create($message['web_app']),
        );
    }
}
