<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SendMediaGroupResponse;
use Tests\Fixture\Benchmark\Model\Shared\InputMediaAudio;
use Tests\Fixture\Benchmark\Model\Shared\InputMediaDocument;
use Tests\Fixture\Benchmark\Model\Shared\InputMediaPhoto;
use Tests\Fixture\Benchmark\Model\Shared\InputMediaVideo;
use Tests\Fixture\Benchmark\Model\Shared\ReplyParameters;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SendMediaGroupResponse>
 */
final readonly class SendMediaGroupRequest implements RequestInterface
{
    use ArrayFilterTrait;

    /**
     * @param InputMediaAudio[]|InputMediaDocument[]|InputMediaPhoto[]|InputMediaVideo[] $media
     */
    public function __construct(
        public int|string $chatId,
        public array $media,
        public ?string $businessConnectionId = null,
        public ?int $messageThreadId = null,
        public ?bool $disableNotification = null,
        public ?bool $protectContent = null,
        public ?bool $allowPaidBroadcast = null,
        public ?string $messageEffectId = null,
        public ?ReplyParameters $replyParameters = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SendMediaGroup;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'                => $this->chatId,
                'media'                  => array_map(
                    static fn (InputMediaAudio|InputMediaDocument|InputMediaPhoto|InputMediaVideo $type,
                    ): array => $type->format(),
                    $this->media,
                ),
                'business_connection_id' => $this->businessConnectionId,
                'message_thread_id'      => $this->messageThreadId,
                'disable_notification'   => $this->disableNotification,
                'protect_content'        => $this->protectContent,
                'allow_paid_broadcast'   => $this->allowPaidBroadcast,
                'message_effect_id'      => $this->messageEffectId,
                'reply_parameters'       => $this->replyParameters?->format() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
