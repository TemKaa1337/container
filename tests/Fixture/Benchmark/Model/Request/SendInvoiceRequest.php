<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SendInvoiceResponse;
use Tests\Fixture\Benchmark\Model\Shared\InlineKeyboardMarkup;
use Tests\Fixture\Benchmark\Model\Shared\LabeledPrice;
use Tests\Fixture\Benchmark\Model\Shared\ReplyParameters;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SendInvoiceResponse>
 */
final readonly class SendInvoiceRequest implements RequestInterface
{
    use ArrayFilterTrait;

    /**
     * @param LabeledPrice[] $prices
     * @param int[]|null     $suggestedTipAmounts
     */
    public function __construct(
        public int|string $chatId,
        public string $title,
        public string $description,
        public string $payload,
        public string $currency,
        public array $prices,
        public ?int $messageThreadId = null,
        public ?string $providerToken = null,
        public ?int $maxTipAmount = null,
        public ?array $suggestedTipAmounts = null,
        public ?string $startParameter = null,
        public ?string $providerData = null,
        public ?string $photoUrl = null,
        public ?int $photoSize = null,
        public ?int $photoWidth = null,
        public ?int $photoHeight = null,
        public ?bool $needName = null,
        public ?bool $needPhoneNumber = null,
        public ?bool $needEmail = null,
        public ?bool $needShippingAddress = null,
        public ?bool $sendPhoneNumberToProvider = null,
        public ?bool $sendEmailToProvider = null,
        public ?bool $isFlexible = null,
        public ?bool $disableNotification = null,
        public ?bool $protectContent = null,
        public ?bool $allowPaidBroadcast = null,
        public ?string $messageEffectId = null,
        public ?ReplyParameters $replyParameters = null,
        public ?InlineKeyboardMarkup $replyMarkup = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SendInvoice;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'                       => $this->chatId,
                'title'                         => $this->title,
                'description'                   => $this->description,
                'payload'                       => $this->payload,
                'currency'                      => $this->currency,
                'prices'                        => array_map(
                    static fn (LabeledPrice $type): array => $type->format(),
                    $this->prices,
                ),
                'message_thread_id'             => $this->messageThreadId,
                'provider_token'                => $this->providerToken,
                'max_tip_amount'                => $this->maxTipAmount,
                'suggested_tip_amounts'         => $this->suggestedTipAmounts,
                'start_parameter'               => $this->startParameter,
                'provider_data'                 => $this->providerData,
                'photo_url'                     => $this->photoUrl,
                'photo_size'                    => $this->photoSize,
                'photo_width'                   => $this->photoWidth,
                'photo_height'                  => $this->photoHeight,
                'need_name'                     => $this->needName,
                'need_phone_number'             => $this->needPhoneNumber,
                'need_email'                    => $this->needEmail,
                'need_shipping_address'         => $this->needShippingAddress,
                'send_phone_number_to_provider' => $this->sendPhoneNumberToProvider,
                'send_email_to_provider'        => $this->sendEmailToProvider,
                'is_flexible'                   => $this->isFlexible,
                'disable_notification'          => $this->disableNotification,
                'protect_content'               => $this->protectContent,
                'allow_paid_broadcast'          => $this->allowPaidBroadcast,
                'message_effect_id'             => $this->messageEffectId,
                'reply_parameters'              => $this->replyParameters?->format() ?: null,
                'reply_markup'                  => $this->replyMarkup?->format() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
