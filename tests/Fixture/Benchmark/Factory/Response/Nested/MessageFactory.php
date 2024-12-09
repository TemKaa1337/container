<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Tests\Fixture\Benchmark\Factory\Shared\InlineKeyboardMarkupFactory;
use Tests\Fixture\Benchmark\Factory\Shared\LinkPreviewOptionsFactory;
use Tests\Fixture\Benchmark\Factory\Shared\MessageEntityFactory;
use Tests\Fixture\Benchmark\Factory\Shared\UserFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\Message;
use Tests\Fixture\Benchmark\Model\Response\Nested\PhotoSize;
use Tests\Fixture\Benchmark\Model\Shared\MessageEntity;
use Tests\Fixture\Benchmark\Model\Shared\User;

final readonly class MessageFactory
{
    private GiveawayCompletedFactory $giveawayCompletedFactory;

    public function __construct(
        private ChatFactory $chatFactory,
        private UserFactory $userFactory,
        private MessageOriginUserFactory $messageOriginUserFactory,
        private MessageOriginHiddenUserFactory $messageOriginHiddenUserFactory,
        private MessageOriginChatFactory $messageOriginChatFactory,
        private MessageOriginChannelFactory $messageOriginChannelFactory,
        private ExternalReplyInfoFactory $externalReplyInfoFactory,
        private TextQuoteFactory $textQuoteFactory,
        private StoryFactory $storyFactory,
        private MessageEntityFactory $messageEntityFactory,
        private LinkPreviewOptionsFactory $linkPreviewOptionsFactory,
        private AnimationFactory $animationFactory,
        private AudioFactory $audioFactory,
        private DocumentFactory $documentFactory,
        private PaidMediaInfoFactory $paidMediaInfoFactory,
        private PhotoSizeFactory $photoSizeFactory,
        private StickerFactory $stickerFactory,
        private VideoFactory $videoFactory,
        private VideoNoteFactory $videoNoteFactory,
        private VoiceFactory $voiceFactory,
        private ContactFactory $contactFactory,
        private DiceFactory $diceFactory,
        private GameFactory $gameFactory,
        private PollFactory $pollFactory,
        private VenueFactory $venueFactory,
        private LocationFactory $locationFactory,
        private MessageAutoDeleteTimerChangedFactory $messageAutoDeleteTimerChangedFactory,
        private InaccessibleMessageFactory $inaccessibleMessageFactory,
        private InvoiceFactory $invoiceFactory,
        private SuccessfulPaymentFactory $successfulPaymentFactory,
        private RefundedPaymentFactory $refundedPaymentFactory,
        private UsersSharedFactory $usersSharedFactory,
        private ChatSharedFactory $chatSharedFactory,
        private WriteAccessAllowedFactory $writeAccessAllowedFactory,
        private PassportDataFactory $passportDataFactory,
        private ProximityAlertTriggeredFactory $proximityAlertTriggeredFactory,
        private ChatBoostAddedFactory $chatBoostAddedFactory,
        private ChatBackgroundFactory $chatBackgroundFactory,
        private ForumTopicCreatedFactory $forumTopicCreatedFactory,
        private ForumTopicEditedFactory $forumTopicEditedFactory,
        private ForumTopicClosedFactory $forumTopicClosedFactory,
        private ForumTopicReopenedFactory $forumTopicReopenedFactory,
        private GeneralForumTopicHiddenFactory $generalForumTopicHiddenFactory,
        private GeneralForumTopicUnhiddenFactory $generalForumTopicUnhiddenFactory,
        private GiveawayCreatedFactory $giveawayCreatedFactory,
        private GiveawayFactory $giveawayFactory,
        private GiveawayWinnersFactory $giveawayWinnersFactory,
        private VideoChatScheduledFactory $videoChatScheduledFactory,
        private VideoChatStartedFactory $videoChatStartedFactory,
        private VideoChatEndedFactory $videoChatEndedFactory,
        private VideoChatParticipantsInvitedFactory $videoChatParticipantsInvitedFactory,
        private WebAppDataFactory $webAppDataFactory,
        private InlineKeyboardMarkupFactory $inlineKeyboardMarkupFactory,
    ) {
        $this->giveawayCompletedFactory = new GiveawayCompletedFactory($this);
    }

    public function create(array $message): Message
    {
        return new Message(
            $message['message_id'],
            (new DateTimeImmutable())->setTimestamp($message['date'])->setTimezone(new DateTimeZone('UTC')),
            $this->chatFactory->create($message['chat']),
            $message['message_thread_id'] ?? null,
            isset($message['from']) ? $this->userFactory->create($message['from']) : null,
            isset($message['sender_chat']) ? $this->chatFactory->create($message['sender_chat']) : null,
            $message['sender_boost_count'] ?? null,
            isset($message['sender_business_bot']) ? $this->userFactory->create($message['sender_business_bot']) : null,
            $message['business_connection_id'] ?? null,
            match (true) {
                !isset($message['forward_origin'])                   => null,
                $message['forward_origin']['type'] === 'user'        => $this->messageOriginUserFactory->create(
                    $message['forward_origin'],
                ),
                $message['forward_origin']['type'] === 'hidden_user' => $this->messageOriginHiddenUserFactory->create(
                    $message['forward_origin'],
                ),
                $message['forward_origin']['type'] === 'chat'        => $this->messageOriginChatFactory->create(
                    $message['forward_origin'],
                ),
                $message['forward_origin']['type'] === 'channel'     => $this->messageOriginChannelFactory->create(
                    $message['forward_origin'],
                ),
                default                                              => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
            $message['is_topic_message'] ?? null,
            $message['is_automatic_forward'] ?? null,
            isset($message['reply_to_message']) ? $this->create($message['reply_to_message']) : null,
            isset($message['external_reply']) ? $this->externalReplyInfoFactory->create(
                $message['external_reply'],
            ) : null,
            isset($message['quote']) ? $this->textQuoteFactory->create($message['quote']) : null,
            isset($message['reply_to_story']) ? $this->storyFactory->create($message['reply_to_story']) : null,
            isset($message['via_bot']) ? $this->userFactory->create($message['via_bot']) : null,
            isset($message['edit_date']) ? (new DateTimeImmutable())->setTimestamp($message['edit_date'])->setTimezone(
                new DateTimeZone('UTC'),
            ) : null,
            $message['has_protected_content'] ?? null,
            $message['is_from_offline'] ?? null,
            $message['media_group_id'] ?? null,
            $message['author_signature'] ?? null,
            $message['text'] ?? null,
            match (true) {
                isset($message['entities']) => array_map(
                    fn (array $nested): MessageEntity => $this->messageEntityFactory->create($nested),
                    $message['entities'],
                ),
                default                     => null,
            },
            isset($message['link_preview_options']) ? $this->linkPreviewOptionsFactory->create(
                $message['link_preview_options'],
            ) : null,
            $message['effect_id'] ?? null,
            isset($message['animation']) ? $this->animationFactory->create($message['animation']) : null,
            isset($message['audio']) ? $this->audioFactory->create($message['audio']) : null,
            isset($message['document']) ? $this->documentFactory->create($message['document']) : null,
            isset($message['paid_media']) ? $this->paidMediaInfoFactory->create($message['paid_media']) : null,
            match (true) {
                isset($message['photo']) => array_map(
                    fn (array $nested): PhotoSize => $this->photoSizeFactory->create($nested),
                    $message['photo'],
                ),
                default                  => null,
            },
            isset($message['sticker']) ? $this->stickerFactory->create($message['sticker']) : null,
            isset($message['story']) ? $this->storyFactory->create($message['story']) : null,
            isset($message['video']) ? $this->videoFactory->create($message['video']) : null,
            isset($message['video_note']) ? $this->videoNoteFactory->create($message['video_note']) : null,
            isset($message['voice']) ? $this->voiceFactory->create($message['voice']) : null,
            $message['caption'] ?? null,
            match (true) {
                isset($message['caption_entities']) => array_map(
                    fn (array $nested): MessageEntity => $this->messageEntityFactory->create($nested),
                    $message['caption_entities'],
                ),
                default                             => null,
            },
            $message['show_caption_above_media'] ?? null,
            $message['has_media_spoiler'] ?? null,
            isset($message['contact']) ? $this->contactFactory->create($message['contact']) : null,
            isset($message['dice']) ? $this->diceFactory->create($message['dice']) : null,
            isset($message['game']) ? $this->gameFactory->create($message['game']) : null,
            isset($message['poll']) ? $this->pollFactory->create($message['poll']) : null,
            isset($message['venue']) ? $this->venueFactory->create($message['venue']) : null,
            isset($message['location']) ? $this->locationFactory->create($message['location']) : null,
            match (true) {
                isset($message['new_chat_members']) => array_map(
                    fn (array $nested): User => $this->userFactory->create($nested),
                    $message['new_chat_members'],
                ),
                default                             => null,
            },
            isset($message['left_chat_member']) ? $this->userFactory->create($message['left_chat_member']) : null,
            $message['new_chat_title'] ?? null,
            match (true) {
                isset($message['new_chat_photo']) => array_map(
                    fn (array $nested): PhotoSize => $this->photoSizeFactory->create($nested),
                    $message['new_chat_photo'],
                ),
                default                           => null,
            },
            $message['delete_chat_photo'] ?? null,
            $message['group_chat_created'] ?? null,
            $message['supergroup_chat_created'] ?? null,
            $message['channel_chat_created'] ?? null,
            isset($message['message_auto_delete_timer_changed']) ? $this->messageAutoDeleteTimerChangedFactory->create(
                $message['message_auto_delete_timer_changed'],
            ) : null,
            $message['migrate_to_chat_id'] ?? null,
            $message['migrate_from_chat_id'] ?? null,
            match (true) {
                !isset($message['pinned_message'])           => null,
                $this->hasAnyKey($message['pinned_message']) => $this->create($message['pinned_message']),
                default                                      => $this->inaccessibleMessageFactory->create(
                    $message['pinned_message'],
                )
            },
            isset($message['invoice']) ? $this->invoiceFactory->create($message['invoice']) : null,
            isset($message['successful_payment']) ? $this->successfulPaymentFactory->create(
                $message['successful_payment'],
            ) : null,
            isset($message['refunded_payment']) ? $this->refundedPaymentFactory->create(
                $message['refunded_payment'],
            ) : null,
            isset($message['users_shared']) ? $this->usersSharedFactory->create($message['users_shared']) : null,
            isset($message['chat_shared']) ? $this->chatSharedFactory->create($message['chat_shared']) : null,
            $message['connected_website'] ?? null,
            isset($message['write_access_allowed']) ? $this->writeAccessAllowedFactory->create(
                $message['write_access_allowed'],
            ) : null,
            isset($message['passport_data']) ? $this->passportDataFactory->create($message['passport_data']) : null,
            isset($message['proximity_alert_triggered']) ? $this->proximityAlertTriggeredFactory->create(
                $message['proximity_alert_triggered'],
            ) : null,
            isset($message['boost_added']) ? $this->chatBoostAddedFactory->create($message['boost_added']) : null,
            isset($message['chat_background_set']) ? $this->chatBackgroundFactory->create(
                $message['chat_background_set'],
            ) : null,
            isset($message['forum_topic_created']) ? $this->forumTopicCreatedFactory->create(
                $message['forum_topic_created'],
            ) : null,
            isset($message['forum_topic_edited']) ? $this->forumTopicEditedFactory->create(
                $message['forum_topic_edited'],
            ) : null,
            isset($message['forum_topic_closed']) ? $this->forumTopicClosedFactory->create(
                $message['forum_topic_closed'],
            ) : null,
            isset($message['forum_topic_reopened']) ? $this->forumTopicReopenedFactory->create(
                $message['forum_topic_reopened'],
            ) : null,
            isset($message['general_forum_topic_hidden']) ? $this->generalForumTopicHiddenFactory->create(
                $message['general_forum_topic_hidden'],
            ) : null,
            isset($message['general_forum_topic_unhidden']) ? $this->generalForumTopicUnhiddenFactory->create(
                $message['general_forum_topic_unhidden'],
            ) : null,
            isset($message['giveaway_created']) ? $this->giveawayCreatedFactory->create(
                $message['giveaway_created'],
            ) : null,
            isset($message['giveaway']) ? $this->giveawayFactory->create($message['giveaway']) : null,
            isset($message['giveaway_winners']) ? $this->giveawayWinnersFactory->create(
                $message['giveaway_winners'],
            ) : null,
            isset($message['giveaway_completed']) ? $this->giveawayCompletedFactory->create(
                $message['giveaway_completed'],
            ) : null,
            isset($message['video_chat_scheduled']) ? $this->videoChatScheduledFactory->create(
                $message['video_chat_scheduled'],
            ) : null,
            isset($message['video_chat_started']) ? $this->videoChatStartedFactory->create(
                $message['video_chat_started'],
            ) : null,
            isset($message['video_chat_ended']) ? $this->videoChatEndedFactory->create(
                $message['video_chat_ended'],
            ) : null,
            isset($message['video_chat_participants_invited']) ? $this->videoChatParticipantsInvitedFactory->create(
                $message['video_chat_participants_invited'],
            ) : null,
            isset($message['web_app_data']) ? $this->webAppDataFactory->create($message['web_app_data']) : null,
            isset($message['reply_markup']) ? $this->inlineKeyboardMarkupFactory->create(
                $message['reply_markup'],
            ) : null,
        );
    }

    /**
     * @param array<string, mixed> $message
     */
    private function hasAnyKey(array $message): bool
    {
        return !empty(
        array_intersect(array_keys($message), [
            'message_thread_id',
            'from',
            'sender_chat',
            'sender_boost_count',
            'sender_business_bot',
            'business_connection_id',
            'forward_origin',
            'is_topic_message',
            'is_automatic_forward',
            'reply_to_message',
            'external_reply',
            'quote',
            'reply_to_story',
            'via_bot',
            'edit_date',
            'has_protected_content',
            'is_from_offline',
            'media_group_id',
            'author_signature',
            'text',
            'entities',
            'link_preview_options',
            'effect_id',
            'animation',
            'audio',
            'document',
            'paid_media',
            'photo',
            'sticker',
            'story',
            'video',
            'video_note',
            'voice',
            'caption',
            'caption_entities',
            'show_caption_above_media',
            'has_media_spoiler',
            'contact',
            'dice',
            'game',
            'poll',
            'venue',
            'location',
            'new_chat_members',
            'left_chat_member',
            'new_chat_title',
            'new_chat_photo',
            'delete_chat_photo',
            'group_chat_created',
            'supergroup_chat_created',
            'channel_chat_created',
            'message_auto_delete_timer_changed',
            'migrate_to_chat_id',
            'migrate_from_chat_id',
            'pinned_message',
            'invoice',
            'successful_payment',
            'refunded_payment',
            'users_shared',
            'chat_shared',
            'connected_website',
            'write_access_allowed',
            'passport_data',
            'proximity_alert_triggered',
            'boost_added',
            'chat_background_set',
            'forum_topic_created',
            'forum_topic_edited',
            'forum_topic_closed',
            'forum_topic_reopened',
            'general_forum_topic_hidden',
            'general_forum_topic_unhidden',
            'giveaway_created',
            'giveaway',
            'giveaway_winners',
            'giveaway_completed',
            'video_chat_scheduled',
            'video_chat_started',
            'video_chat_ended',
            'video_chat_participants_invited',
            'web_app_data',
            'reply_markup',
        ])
        );
    }
}
