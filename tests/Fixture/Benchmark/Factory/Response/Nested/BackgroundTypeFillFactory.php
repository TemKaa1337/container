<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use InvalidArgumentException;
use Tests\Fixture\Benchmark\Model\Response\Nested\BackgroundTypeFill;

final readonly class BackgroundTypeFillFactory
{
    public function __construct(
        private BackgroundFillSolidFactory $backgroundFillSolidFactory,
        private BackgroundFillGradientFactory $backgroundFillGradientFactory,
        private BackgroundFillFreeformGradientFactory $backgroundFillFreeformGradientFactory,
    ) {
    }

    public function create(array $message): BackgroundTypeFill
    {
        return new BackgroundTypeFill(
            $message['type'],
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
            $message['dark_theme_dimming'],
        );
    }
}
