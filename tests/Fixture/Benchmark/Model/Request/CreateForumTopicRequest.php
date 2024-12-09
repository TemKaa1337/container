<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\CreateForumTopicResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<CreateForumTopicResponse>
 */
final readonly class CreateForumTopicRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public string $name,
        public ?int $iconColor = null,
        public ?string $iconCustomEmojiId = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::CreateForumTopic;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'              => $this->chatId,
                'name'                 => $this->name,
                'icon_color'           => $this->iconColor,
                'icon_custom_emoji_id' => $this->iconCustomEmojiId,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
