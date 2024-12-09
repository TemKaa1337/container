<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SendDiceResponse;
use Tests\Fixture\Benchmark\Model\Shared\ForceReply;
use Tests\Fixture\Benchmark\Model\Shared\InlineKeyboardMarkup;
use Tests\Fixture\Benchmark\Model\Shared\ReplyKeyboardMarkup;
use Tests\Fixture\Benchmark\Model\Shared\ReplyKeyboardRemove;
use Tests\Fixture\Benchmark\Model\Shared\ReplyParameters;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SendDiceResponse>
 */
final readonly class SendDiceRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public ?string $businessConnectionId = null,
        public ?int $messageThreadId = null,
        public ?string $emoji = null,
        public ?bool $disableNotification = null,
        public ?bool $protectContent = null,
        public ?bool $allowPaidBroadcast = null,
        public ?string $messageEffectId = null,
        public ?ReplyParameters $replyParameters = null,
        public ForceReply|InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|null $replyMarkup = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SendDice;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'                => $this->chatId,
                'business_connection_id' => $this->businessConnectionId,
                'message_thread_id'      => $this->messageThreadId,
                'emoji'                  => $this->emoji,
                'disable_notification'   => $this->disableNotification,
                'protect_content'        => $this->protectContent,
                'allow_paid_broadcast'   => $this->allowPaidBroadcast,
                'message_effect_id'      => $this->messageEffectId,
                'reply_parameters'       => $this->replyParameters?->format() ?: null,
                'reply_markup'           => $this->replyMarkup?->format() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
