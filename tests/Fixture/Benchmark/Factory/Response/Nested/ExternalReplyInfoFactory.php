<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response\Nested;

use InvalidArgumentException;
use Tests\Fixture\Benchmark\Factory\Shared\LinkPreviewOptionsFactory;
use Tests\Fixture\Benchmark\Model\Response\Nested\ExternalReplyInfo;
use Tests\Fixture\Benchmark\Model\Response\Nested\PhotoSize;

final readonly class ExternalReplyInfoFactory
{
    public function __construct(
        private MessageOriginUserFactory $messageOriginUserFactory,
        private MessageOriginHiddenUserFactory $messageOriginHiddenUserFactory,
        private MessageOriginChatFactory $messageOriginChatFactory,
        private MessageOriginChannelFactory $messageOriginChannelFactory,
        private ChatFactory $chatFactory,
        private LinkPreviewOptionsFactory $linkPreviewOptionsFactory,
        private AnimationFactory $animationFactory,
        private AudioFactory $audioFactory,
        private DocumentFactory $documentFactory,
        private PaidMediaInfoFactory $paidMediaInfoFactory,
        private PhotoSizeFactory $photoSizeFactory,
        private StickerFactory $stickerFactory,
        private StoryFactory $storyFactory,
        private VideoFactory $videoFactory,
        private VideoNoteFactory $videoNoteFactory,
        private VoiceFactory $voiceFactory,
        private ContactFactory $contactFactory,
        private DiceFactory $diceFactory,
        private GameFactory $gameFactory,
        private GiveawayFactory $giveawayFactory,
        private GiveawayWinnersFactory $giveawayWinnersFactory,
        private InvoiceFactory $invoiceFactory,
        private LocationFactory $locationFactory,
        private PollFactory $pollFactory,
        private VenueFactory $venueFactory,
    ) {
    }

    public function create(array $message): ExternalReplyInfo
    {
        return new ExternalReplyInfo(
            match (true) {
                $message['origin']['type'] === 'user' => $this->messageOriginUserFactory->create(
                    $message['origin'],
                ),
                $message['origin']['type'] === 'hidden_user' => $this->messageOriginHiddenUserFactory->create(
                    $message['origin'],
                ),
                $message['origin']['type'] === 'chat' => $this->messageOriginChatFactory->create(
                    $message['origin'],
                ),
                $message['origin']['type'] === 'channel' => $this->messageOriginChannelFactory->create(
                    $message['origin'],
                ),
                default => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
            isset($message['chat']) ? $this->chatFactory->create($message['chat']) : null,
            $message['message_id'] ?? null,
            isset($message['link_preview_options']) ? $this->linkPreviewOptionsFactory->create(
                $message['link_preview_options'],
            ) : null,
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
            $message['has_media_spoiler'] ?? null,
            isset($message['contact']) ? $this->contactFactory->create($message['contact']) : null,
            isset($message['dice']) ? $this->diceFactory->create($message['dice']) : null,
            isset($message['game']) ? $this->gameFactory->create($message['game']) : null,
            isset($message['giveaway']) ? $this->giveawayFactory->create($message['giveaway']) : null,
            isset($message['giveaway_winners']) ? $this->giveawayWinnersFactory->create(
                $message['giveaway_winners'],
            ) : null,
            isset($message['invoice']) ? $this->invoiceFactory->create($message['invoice']) : null,
            isset($message['location']) ? $this->locationFactory->create($message['location']) : null,
            isset($message['poll']) ? $this->pollFactory->create($message['poll']) : null,
            isset($message['venue']) ? $this->venueFactory->create($message['venue']) : null,
        );
    }
}
