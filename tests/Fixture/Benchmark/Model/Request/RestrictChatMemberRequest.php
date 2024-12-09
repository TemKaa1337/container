<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\RestrictChatMemberResponse;
use Tests\Fixture\Benchmark\Model\Shared\ChatPermissions;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<RestrictChatMemberResponse>
 */
final readonly class RestrictChatMemberRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public int $userId,
        public ChatPermissions $permissions,
        public ?bool $useIndependentChatPermissions = null,
        public ?DateTimeImmutable $untilDate = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::RestrictChatMember;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'                          => $this->chatId,
                'user_id'                          => $this->userId,
                'permissions'                      => $this->permissions->format(),
                'use_independent_chat_permissions' => $this->useIndependentChatPermissions,
                'until_date'                       => $this->untilDate?->setTimezone(
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
