<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\MessageEntityFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\Game;
use Tests\Fixture\Benchmark\Model\Response\Nested\PhotoSize;
use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;

final readonly class GameFactory
{
    public function __construct(
        private PhotoSizeFactory $photoSizeFactory,
        private MessageEntityFactory $messageEntityFactory,
        private AnimationFactory $animationFactory,
    ) {
    }

    public function create(array $message): Game
    {
        return new Game(
            $message['title'],
            $message['description'],
            array_map(fn (array $nested): PhotoSize => $this->photoSizeFactory->create($nested), $message['photo']),
            $message['text'] ?? null,
            match (true) {
                isset($message['text_entities']) => array_map(
                    fn (array $nested): MessageEntity => $this->messageEntityFactory->create($nested),
                    $message['text_entities'],
                ),
                default                          => null,
            },
            isset($message['animation']) ? $this->animationFactory->create($message['animation']) : null,
        );
    }
}
