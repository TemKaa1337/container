<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\BanChatMemberResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<BanChatMemberResponse>
 */
final readonly class BanChatMemberRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public int $userId,
        public ?DateTimeImmutable $untilDate = null,
        public ?bool $revokeMessages = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::BanChatMember;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'         => $this->chatId,
                'user_id'         => $this->userId,
                'until_date'      => $this->untilDate?->setTimezone(new DateTimeZone('UTC'))?->getTimestamp() ?: null,
                'revoke_messages' => $this->revokeMessages,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
