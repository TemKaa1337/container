<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use InvalidArgumentException;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatBackground;

final readonly class ChatBackgroundFactory
{
    public function __construct(
        private BackgroundTypeFillFactory $backgroundTypeFillFactory,
        private BackgroundTypeWallpaperFactory $backgroundTypeWallpaperFactory,
        private BackgroundTypePatternFactory $backgroundTypePatternFactory,
        private BackgroundTypeChatThemeFactory $backgroundTypeChatThemeFactory,
    ) {
    }

    public function create(array $message): ChatBackground
    {
        return new ChatBackground(
            match (true) {
                $message['type']['type'] === 'fill'       => $this->backgroundTypeFillFactory->create($message['type']),
                $message['type']['type'] === 'wallpaper'  => $this->backgroundTypeWallpaperFactory->create(
                    $message['type'],
                ),
                $message['type']['type'] === 'pattern'    => $this->backgroundTypePatternFactory->create(
                    $message['type'],
                ),
                $message['type']['type'] === 'chat_theme' => $this->backgroundTypeChatThemeFactory->create(
                    $message['type'],
                ),
                default                                   => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
        );
    }
}
