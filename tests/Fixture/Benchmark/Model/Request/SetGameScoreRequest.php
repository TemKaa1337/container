<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetGameScoreResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetGameScoreResponse>
 */
final readonly class SetGameScoreRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int $userId,
        public int $score,
        public ?bool $force = null,
        public ?bool $disableEditMessage = null,
        public ?int $chatId = null,
        public ?int $messageId = null,
        public ?string $inlineMessageId = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetGameScore;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'user_id'              => $this->userId,
                'score'                => $this->score,
                'force'                => $this->force,
                'disable_edit_message' => $this->disableEditMessage,
                'chat_id'              => $this->chatId,
                'message_id'           => $this->messageId,
                'inline_message_id'    => $this->inlineMessageId,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
