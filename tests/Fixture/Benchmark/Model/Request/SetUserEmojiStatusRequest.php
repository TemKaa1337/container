<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetUserEmojiStatusResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetUserEmojiStatusResponse>
 */
final readonly class SetUserEmojiStatusRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int $userId,
        public ?string $emojiStatusCustomEmojiId = null,
        public ?DateTimeImmutable $emojiStatusExpirationDate = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetUserEmojiStatus;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'user_id' => $this->userId,
                'emoji_status_custom_emoji_id' => $this->emojiStatusCustomEmojiId,
                'emoji_status_expiration_date' => $this->emojiStatusExpirationDate?->setTimezone(
                    new DateTimeZone('UTC'),
                )?->getTimestamp() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
