<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\CreateChatInviteLinkResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<CreateChatInviteLinkResponse>
 */
final readonly class CreateChatInviteLinkRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public ?string $name = null,
        public ?DateTimeImmutable $expireDate = null,
        public ?int $memberLimit = null,
        public ?bool $createsJoinRequest = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::CreateChatInviteLink;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'              => $this->chatId,
                'name'                 => $this->name,
                'expire_date'          => $this->expireDate?->setTimezone(new DateTimeZone('UTC'))?->getTimestamp(
                ) ?: null,
                'member_limit'         => $this->memberLimit,
                'creates_join_request' => $this->createsJoinRequest,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
