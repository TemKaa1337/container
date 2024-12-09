<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response\Nested;

final readonly class Update
{
    public function __construct(
        public int $updateId,
        public ?Message $message = null,
        public ?Message $editedMessage = null,
        public ?Message $channelPost = null,
        public ?Message $editedChannelPost = null,
        public ?BusinessConnection $businessConnection = null,
        public ?Message $businessMessage = null,
        public ?Message $editedBusinessMessage = null,
        public ?BusinessMessagesDeleted $deletedBusinessMessages = null,
        public ?MessageReactionUpdated $messageReaction = null,
        public ?MessageReactionCountUpdated $messageReactionCount = null,
        public ?InlineQuery $inlineQuery = null,
        public ?ChosenInlineResult $chosenInlineResult = null,
        public ?CallbackQuery $callbackQuery = null,
        public ?ShippingQuery $shippingQuery = null,
        public ?PreCheckoutQuery $preCheckoutQuery = null,
        public ?PaidMediaPurchased $purchasedPaidMedia = null,
        public ?Poll $poll = null,
        public ?PollAnswer $pollAnswer = null,
        public ?ChatMemberUpdated $myChatMember = null,
        public ?ChatMemberUpdated $chatMember = null,
        public ?ChatJoinRequest $chatJoinRequest = null,
        public ?ChatBoostUpdated $chatBoost = null,
        public ?ChatBoostRemoved $removedChatBoost = null,
    ) {
    }
}
