<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetMessageReactionResponse;
use Tests\Fixture\Benchmark\Model\Shared\ReactionTypeCustomEmoji;
use Tests\Fixture\Benchmark\Model\Shared\ReactionTypeEmoji;
use Tests\Fixture\Benchmark\Model\Shared\ReactionTypePaid;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetMessageReactionResponse>
 */
final readonly class SetMessageReactionRequest implements RequestInterface
{
    use ArrayFilterTrait;

    /**
     * @param ReactionTypeEmoji[]|ReactionTypeCustomEmoji[]|ReactionTypePaid[]|null $reaction
     */
    public function __construct(
        public int|string $chatId,
        public int $messageId,
        public ?array $reaction = null,
        public ?bool $isBig = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetMessageReaction;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'    => $this->chatId,
                'message_id' => $this->messageId,
                'reaction'   => $this->reaction === null
                    ? null
                    : array_map(
                        static fn (ReactionTypeEmoji|ReactionTypeCustomEmoji|ReactionTypePaid $type,
                        ): array => $type->format(),
                        $this->reaction,
                    ),
                'is_big'     => $this->isBig,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
