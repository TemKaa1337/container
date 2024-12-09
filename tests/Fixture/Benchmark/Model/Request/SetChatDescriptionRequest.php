<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetChatDescriptionResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetChatDescriptionResponse>
 */
final readonly class SetChatDescriptionRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public ?string $description = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetChatDescription;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'     => $this->chatId,
                'description' => $this->description,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
