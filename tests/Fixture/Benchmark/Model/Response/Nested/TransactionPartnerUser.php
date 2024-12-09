<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class TransactionPartnerUser
{
    /**
     * @param PaidMediaPreview[]|PaidMediaPhoto[]|PaidMediaVideo[]|null $paidMedia
     */
    public function __construct(
        public string $type,
        public User $user,
        public ?AffiliateInfo $affiliate = null,
        public ?string $invoicePayload = null,
        public ?int $subscriptionPeriod = null,
        public ?array $paidMedia = null,
        public ?string $paidMediaPayload = null,
        public ?Gift $gift = null,
    ) {
    }
}
