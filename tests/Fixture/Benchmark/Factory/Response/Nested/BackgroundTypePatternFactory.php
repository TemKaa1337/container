<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use InvalidArgumentException;
use Tests\Fixture\Benchmark\Model\Response\Nested\BackgroundTypePattern;

final readonly class BackgroundTypePatternFactory
{
    public function __construct(
        private DocumentFactory $documentFactory,
        private BackgroundFillSolidFactory $backgroundFillSolidFactory,
        private BackgroundFillGradientFactory $backgroundFillGradientFactory,
        private BackgroundFillFreeformGradientFactory $backgroundFillFreeformGradientFactory,
    ) {
    }

    public function create(array $message): BackgroundTypePattern
    {
        return new BackgroundTypePattern(
            $message['type'],
            $this->documentFactory->create($message['document']),
            match (true) {
                $message['fill']['type'] === 'solid'             => $this->backgroundFillSolidFactory->create(
                    $message['fill'],
                ),
                $message['fill']['type'] === 'gradient'          => $this->backgroundFillGradientFactory->create(
                    $message['fill'],
                ),
                $message['fill']['type'] === 'freeform_gradient' => $this->backgroundFillFreeformGradientFactory->create(
                    $message['fill'],
                ),
                default                                          => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
            $message['intensity'],
            $message['is_inverted'] ?? null,
            $message['is_moving'] ?? null,
        );
    }
}
