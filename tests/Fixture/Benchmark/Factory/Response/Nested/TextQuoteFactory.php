<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Factory\Shared\MessageEntityFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\TextQuote;
use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;

final readonly class TextQuoteFactory
{
    public function __construct(private MessageEntityFactory $messageEntityFactory)
    {
    }

    public function create(array $message): TextQuote
    {
        return new TextQuote(
            $message['text'],
            $message['position'],
            match (true) {
                isset($message['entities']) => array_map(
                    fn (array $nested): MessageEntity => $this->messageEntityFactory->create($nested),
                    $message['entities'],
                ),
                default                     => null,
            },
            $message['is_manual'] ?? null,
        );
    }
}
