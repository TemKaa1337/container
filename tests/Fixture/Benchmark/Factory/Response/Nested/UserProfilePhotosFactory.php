<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\PhotoSize;
use Tests\Fixture\Benchmark\Model\Response\Nested\UserProfilePhotos;

final readonly class UserProfilePhotosFactory
{
    public function __construct(private PhotoSizeFactory $photoSizeFactory)
    {
    }

    public function create(array $message): UserProfilePhotos
    {
        return new UserProfilePhotos(
            $message['total_count'],
            array_map(
                fn (array $row): array => array_map(
                    fn (array $column): PhotoSize => $this->photoSizeFactory->create($column),
                    $row,
                ),
                $message['photos'],
            ),
        );
    }
}
