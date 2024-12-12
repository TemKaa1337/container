<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use InvalidArgumentException;
use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\PaidMediaPhoto;
use Tests\Fixture\Benchmark\Model\Response\Nested\PaidMediaPreview;
use Tests\Fixture\Benchmark\Model\Response\Nested\PaidMediaVideo;
use Tests\Fixture\Benchmark\Model\Response\Nested\TransactionPartnerUser;

final readonly class TransactionPartnerUserFactory
{
    public function __construct(
        private UserFactory $userFactory,
        private AffiliateInfoFactory $affiliateInfoFactory,
        private PaidMediaPreviewFactory $paidMediaPreviewFactory,
        private PaidMediaPhotoFactory $paidMediaPhotoFactory,
        private PaidMediaVideoFactory $paidMediaVideoFactory,
        private GiftFactory $giftFactory,
    ) {
    }

    public function create(array $message): TransactionPartnerUser
    {
        $factory = match (true) {
            !isset($message['paid_media'])                                        => null,
            is_array($message['paid_media']) && $message[0]['type'] === 'preview' => $this->paidMediaPreviewFactory,
            is_array($message['paid_media']) && $message[0]['type'] === 'photo'   => $this->paidMediaPhotoFactory,
            is_array($message['paid_media']) && $message[0]['type'] === 'video'   => $this->paidMediaVideoFactory,
            default                                                               => null,
        };

        return new TransactionPartnerUser(
            $message['type'],
            $this->userFactory->create($message['user']),
            isset($message['affiliate']) ? $this->affiliateInfoFactory->create($message['affiliate']) : null,
            $message['invoice_payload'] ?? null,
            $message['subscription_period'] ?? null,
            match (true) {
                !isset($message['paid_media']) => null,
                $factory !== null              => array_map(
                    static fn (array $nested): PaidMediaPreview|PaidMediaPhoto|PaidMediaVideo => $factory->create(
                        $nested,
                    ),
                    $message['paid_media'],
                ),
                default                        => throw new InvalidArgumentException(
                    sprintf('Could not find factory for message in factory: "%s".', self::class),
                )
            },
            $message['paid_media_payload'] ?? null,
            isset($message['gift']) ? $this->giftFactory->create($message['gift']) : null,
        );
    }
}
