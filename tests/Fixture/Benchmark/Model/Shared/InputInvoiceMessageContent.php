<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Shared;

use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

final readonly class InputInvoiceMessageContent
{
    use ArrayFilterTrait;

    /**
     * @param LabeledPrice[] $prices
     * @param int[]|null     $suggestedTipAmounts
     */
    public function __construct(
        public string $title,
        public string $description,
        public string $payload,
        public string $currency,
        public array $prices,
        public ?string $providerToken = null,
        public ?int $maxTipAmount = null,
        public ?array $suggestedTipAmounts = null,
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
    ) {
    }

    public function format(): array
    {
        return $this->filterNullable(
            [
                'title'                         => $this->title,
                'description'                   => $this->description,
                'payload'                       => $this->payload,
                'currency'                      => $this->currency,
                'prices'                        => array_map(
                    static fn (LabeledPrice $type): array => $type->format(),
                    $this->prices,
                ),
                'provider_token'                => $this->providerToken,
                'max_tip_amount'                => $this->maxTipAmount,
                'suggested_tip_amounts'         => $this->suggestedTipAmounts,
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
            ],
        );
    }
}
