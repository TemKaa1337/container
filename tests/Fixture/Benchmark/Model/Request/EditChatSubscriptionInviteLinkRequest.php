<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\EditChatSubscriptionInviteLinkResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<EditChatSubscriptionInviteLinkResponse>
 */
final readonly class EditChatSubscriptionInviteLinkRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public string $inviteLink,
        public ?string $name = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::EditChatSubscriptionInviteLink;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'     => $this->chatId,
                'invite_link' => $this->inviteLink,
                'name'        => $this->name,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
