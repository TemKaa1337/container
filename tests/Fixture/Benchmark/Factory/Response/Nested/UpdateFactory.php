<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use Tests\Fixture\Benchmark\Model\Response\Nested\Update;

final readonly class UpdateFactory
{
    public function __construct(
        private MessageFactory $messageFactory,
        private BusinessConnectionFactory $businessConnectionFactory,
        private BusinessMessagesDeletedFactory $businessMessagesDeletedFactory,
        private MessageReactionUpdatedFactory $messageReactionUpdatedFactory,
        private MessageReactionCountUpdatedFactory $messageReactionCountUpdatedFactory,
        private InlineQueryFactory $inlineQueryFactory,
        private ChosenInlineResultFactory $chosenInlineResultFactory,
        private CallbackQueryFactory $callbackQueryFactory,
        private ShippingQueryFactory $shippingQueryFactory,
        private PreCheckoutQueryFactory $preCheckoutQueryFactory,
        private PaidMediaPurchasedFactory $paidMediaPurchasedFactory,
        private PollFactory $pollFactory,
        private PollAnswerFactory $pollAnswerFactory,
        private ChatMemberUpdatedFactory $chatMemberUpdatedFactory,
        private ChatJoinRequestFactory $chatJoinRequestFactory,
        private ChatBoostUpdatedFactory $chatBoostUpdatedFactory,
        private ChatBoostRemovedFactory $chatBoostRemovedFactory,
    ) {
    }

    public function create(array $message): Update
    {
        return new Update(
            $message['update_id'],
            isset($message['message']) ? $this->messageFactory->create($message['message']) : null,
            isset($message['edited_message']) ? $this->messageFactory->create($message['edited_message']) : null,
            isset($message['channel_post']) ? $this->messageFactory->create($message['channel_post']) : null,
            isset($message['edited_channel_post']) ? $this->messageFactory->create(
                $message['edited_channel_post'],
            ) : null,
            isset($message['business_connection']) ? $this->businessConnectionFactory->create(
                $message['business_connection'],
            ) : null,
            isset($message['business_message']) ? $this->messageFactory->create($message['business_message']) : null,
            isset($message['edited_business_message']) ? $this->messageFactory->create(
                $message['edited_business_message'],
            ) : null,
            isset($message['deleted_business_messages']) ? $this->businessMessagesDeletedFactory->create(
                $message['deleted_business_messages'],
            ) : null,
            isset($message['message_reaction']) ? $this->messageReactionUpdatedFactory->create(
                $message['message_reaction'],
            ) : null,
            isset($message['message_reaction_count']) ? $this->messageReactionCountUpdatedFactory->create(
                $message['message_reaction_count'],
            ) : null,
            isset($message['inline_query']) ? $this->inlineQueryFactory->create($message['inline_query']) : null,
            isset($message['chosen_inline_result']) ? $this->chosenInlineResultFactory->create(
                $message['chosen_inline_result'],
            ) : null,
            isset($message['callback_query']) ? $this->callbackQueryFactory->create($message['callback_query']) : null,
            isset($message['shipping_query']) ? $this->shippingQueryFactory->create($message['shipping_query']) : null,
            isset($message['pre_checkout_query']) ? $this->preCheckoutQueryFactory->create(
                $message['pre_checkout_query'],
            ) : null,
            isset($message['purchased_paid_media']) ? $this->paidMediaPurchasedFactory->create(
                $message['purchased_paid_media'],
            ) : null,
            isset($message['poll']) ? $this->pollFactory->create($message['poll']) : null,
            isset($message['poll_answer']) ? $this->pollAnswerFactory->create($message['poll_answer']) : null,
            isset($message['my_chat_member']) ? $this->chatMemberUpdatedFactory->create(
                $message['my_chat_member'],
            ) : null,
            isset($message['chat_member']) ? $this->chatMemberUpdatedFactory->create($message['chat_member']) : null,
            isset($message['chat_join_request']) ? $this->chatJoinRequestFactory->create(
                $message['chat_join_request'],
            ) : null,
            isset($message['chat_boost']) ? $this->chatBoostUpdatedFactory->create($message['chat_boost']) : null,
            isset($message['removed_chat_boost']) ? $this->chatBoostRemovedFactory->create(
                $message['removed_chat_boost'],
            ) : null,
        );
    }
}
