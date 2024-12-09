<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\BackgroundTypeChatTheme;

final readonly class BackgroundTypeChatThemeFactory
{
    public function create(array $message): BackgroundTypeChatTheme
    {
        return new BackgroundTypeChatTheme(
            $message['type'],
            $message['theme_name'],
        );
    }
}
