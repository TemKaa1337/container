<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\RevokeChatInviteLinkResponse;

/**
 * @api
 *
 * @implements RequestInterface<RevokeChatInviteLinkResponse>
 */
final readonly class RevokeChatInviteLinkRequest implements RequestInterface
{
    public function __construct(
        public int|string $chatId,
        public string $inviteLink,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::RevokeChatInviteLink;
    }

    public function getData(): array
    {
        return [
            'chat_id'     => $this->chatId,
            'invite_link' => $this->inviteLink,
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
