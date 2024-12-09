<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\ChatPhoto;

final readonly class ChatPhotoFactory
{
    public function create(array $message): ChatPhoto
    {
        return new ChatPhoto(
            $message['small_file_id'],
            $message['small_file_unique_id'],
            $message['big_file_id'],
            $message['big_file_unique_id'],
        );
    }
}
