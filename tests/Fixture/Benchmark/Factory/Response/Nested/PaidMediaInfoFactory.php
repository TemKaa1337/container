<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use InvalidArgumentException;
use Tests\Fixture\Benchmark\Model\Response\Nested\PaidMediaInfo;
use Tests\Fixture\Benchmark\Model\Response\Nested\PaidMediaPhoto;
use Tests\Fixture\Benchmark\Model\Response\Nested\PaidMediaPreview;
use Tests\Fixture\Benchmark\Model\Response\Nested\PaidMediaVideo;

final readonly class PaidMediaInfoFactory
{
    public function __construct(
        private PaidMediaPreviewFactory $paidMediaPreviewFactory,
        private PaidMediaPhotoFactory $paidMediaPhotoFactory,
        private PaidMediaVideoFactory $paidMediaVideoFactory,
    ) {
    }

    public function create(array $message): PaidMediaInfo
    {
        $factory = match (true) {
            is_array($message['paid_media']) && $message[0]['type'] === 'preview' => $this->paidMediaPreviewFactory,
            is_array($message['paid_media']) && $message[0]['type'] === 'photo'   => $this->paidMediaPhotoFactory,
            is_array($message['paid_media']) && $message[0]['type'] === 'video'   => $this->paidMediaVideoFactory,
            default                                                               => null,
        };

        return new PaidMediaInfo(
            $message['star_count'],
            match (true) {
                $factory !== null => array_map(
                    static fn (array $nested): PaidMediaPreview|PaidMediaPhoto|PaidMediaVideo => $factory->create(
                        $nested,
                    ),
                    $message['paid_media'],
                ),
                default           => throw new InvalidArgumentException(
                    sprintf('Could not find factory for message in factory: "%s".', self::class),
                )
            },
        );
    }
}
