<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\GetGameHighScoresResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<GetGameHighScoresResponse>
 */
final readonly class GetGameHighScoresRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int $userId,
        public ?int $chatId = null,
        public ?int $messageId = null,
        public ?string $inlineMessageId = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::GetGameHighScores;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'user_id'           => $this->userId,
                'chat_id'           => $this->chatId,
                'message_id'        => $this->messageId,
                'inline_message_id' => $this->inlineMessageId,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
