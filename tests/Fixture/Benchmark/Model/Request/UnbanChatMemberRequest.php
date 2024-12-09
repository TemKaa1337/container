<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\UnbanChatMemberResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<UnbanChatMemberResponse>
 */
final readonly class UnbanChatMemberRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public int $userId,
        public ?bool $onlyIfBanned = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::UnbanChatMember;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'        => $this->chatId,
                'user_id'        => $this->userId,
                'only_if_banned' => $this->onlyIfBanned,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
