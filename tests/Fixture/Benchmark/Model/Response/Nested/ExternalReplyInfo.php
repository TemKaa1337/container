<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

use Tests\Fixture\Benchmark\Model\Shared\LinkPreviewOptions;

final readonly class ExternalReplyInfo
{
    /**
     * @param PhotoSize[]|null $photo
     */
    public function __construct(
        public MessageOriginUser|MessageOriginHiddenUser|MessageOriginChat|MessageOriginChannel $origin,
        public ?Chat $chat = null,
        public ?int $messageId = null,
        public ?LinkPreviewOptions $linkPreviewOptions = null,
        public ?Animation $animation = null,
        public ?Audio $audio = null,
        public ?Document $document = null,
        public ?PaidMediaInfo $paidMedia = null,
        public ?array $photo = null,
        public ?Sticker $sticker = null,
        public ?Story $story = null,
        public ?Video $video = null,
        public ?VideoNote $videoNote = null,
        public ?Voice $voice = null,
        public ?true $hasMediaSpoiler = null,
        public ?Contact $contact = null,
        public ?Dice $dice = null,
        public ?Game $game = null,
        public ?Giveaway $giveaway = null,
        public ?GiveawayWinners $giveawayWinners = null,
        public ?Invoice $invoice = null,
        public ?Location $location = null,
        public ?Poll $poll = null,
        public ?Venue $venue = null,
    ) {
    }
}
