<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetChatPermissionsResponse;
use Tests\Fixture\Benchmark\Model\Shared\ChatPermissions;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetChatPermissionsResponse>
 */
final readonly class SetChatPermissionsRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public ChatPermissions $permissions,
        public ?bool $useIndependentChatPermissions = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetChatPermissions;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'                          => $this->chatId,
                'permissions'                      => $this->permissions->format(),
                'use_independent_chat_permissions' => $this->useIndependentChatPermissions,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
