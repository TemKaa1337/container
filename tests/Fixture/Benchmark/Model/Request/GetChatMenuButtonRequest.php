<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\GetChatMenuButtonResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<GetChatMenuButtonResponse>
 */
final readonly class GetChatMenuButtonRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(public ?int $chatId = null)
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::GetChatMenuButton;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id' => $this->chatId,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
