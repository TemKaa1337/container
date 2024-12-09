<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Shared;

use Tests\Fixture\Benchmark\Model\Shared\WebAppInfo;

final readonly class WebAppInfoFactory
{
    public function create(array $message): WebAppInfo
    {
        return new WebAppInfo(
            $message['url'],
        );
    }
}
