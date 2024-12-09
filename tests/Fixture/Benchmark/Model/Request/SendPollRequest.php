<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use DateTimeImmutable;
use DateTimeZone;
use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SendPollResponse;
use Tests\Fixture\Benchmark\Model\Shared\ForceReply;
use Tests\Fixture\Benchmark\Model\Shared\InlineKeyboardMarkup;
use Tests\Fixture\Benchmark\Model\Shared\InputPollOption;
use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;
use Tests\Fixture\Benchmark\Model\Shared\ReplyKeyboardMarkup;
use Tests\Fixture\Benchmark\Model\Shared\ReplyKeyboardRemove;
use Tests\Fixture\Benchmark\Model\Shared\ReplyParameters;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SendPollResponse>
 */
final readonly class SendPollRequest implements RequestInterface
{
    use ArrayFilterTrait;

    /**
     * @param InputPollOption[]    $options
     * @param MessageEntity[]|null $questionEntities
     * @param MessageEntity[]|null $explanationEntities
     */
    public function __construct(
        public int|string $chatId,
        public string $question,
        public array $options,
        public ?string $businessConnectionId = null,
        public ?int $messageThreadId = null,
        public ?string $questionParseMode = null,
        public ?array $questionEntities = null,
        public ?bool $isAnonymous = null,
        public ?string $type = null,
        public ?bool $allowsMultipleAnswers = null,
        public ?int $correctOptionId = null,
        public ?string $explanation = null,
        public ?string $explanationParseMode = null,
        public ?array $explanationEntities = null,
        public ?int $openPeriod = null,
        public ?DateTimeImmutable $closeDate = null,
        public ?bool $isClosed = null,
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
        return ApiMethod::SendPoll;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'                 => $this->chatId,
                'question'                => $this->question,
                'options'                 => array_map(
                    static fn (InputPollOption $type): array => $type->format(),
                    $this->options,
                ),
                'business_connection_id'  => $this->businessConnectionId,
                'message_thread_id'       => $this->messageThreadId,
                'question_parse_mode'     => $this->questionParseMode,
                'question_entities'       => $this->questionEntities === null
                    ? null
                    : array_map(
                        static fn (MessageEntity $type): array => $type->format(),
                        $this->questionEntities,
                    ),
                'is_anonymous'            => $this->isAnonymous,
                'type'                    => $this->type,
                'allows_multiple_answers' => $this->allowsMultipleAnswers,
                'correct_option_id'       => $this->correctOptionId,
                'explanation'             => $this->explanation,
                'explanation_parse_mode'  => $this->explanationParseMode,
                'explanation_entities'    => $this->explanationEntities === null
                    ? null
                    : array_map(
                        static fn (MessageEntity $type): array => $type->format(),
                        $this->explanationEntities,
                    ),
                'open_period'             => $this->openPeriod,
                'close_date'              => $this->closeDate?->setTimezone(new DateTimeZone('UTC'))?->getTimestamp(
                ) ?: null,
                'is_closed'               => $this->isClosed,
                'disable_notification'    => $this->disableNotification,
                'protect_content'         => $this->protectContent,
                'allow_paid_broadcast'    => $this->allowPaidBroadcast,
                'message_effect_id'       => $this->messageEffectId,
                'reply_parameters'        => $this->replyParameters?->format() ?: null,
                'reply_markup'            => $this->replyMarkup?->format() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
