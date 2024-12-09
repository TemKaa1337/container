<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SavePreparedInlineMessageResponse;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultArticle;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultAudio;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultCachedAudio;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultCachedDocument;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultCachedGif;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultCachedMpeg4Gif;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultCachedPhoto;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultCachedSticker;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultCachedVideo;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultCachedVoice;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultContact;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultDocument;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultGame;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultGif;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultLocation;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultMpeg4Gif;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultPhoto;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultVenue;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultVideo;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultVoice;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SavePreparedInlineMessageResponse>
 */
final readonly class SavePreparedInlineMessageRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int $userId,
        public InlineQueryResultCachedAudio|InlineQueryResultCachedDocument|InlineQueryResultCachedGif|InlineQueryResultCachedMpeg4Gif|InlineQueryResultCachedPhoto|InlineQueryResultCachedSticker|InlineQueryResultCachedVideo|InlineQueryResultCachedVoice|InlineQueryResultArticle|InlineQueryResultAudio|InlineQueryResultContact|InlineQueryResultGame|InlineQueryResultDocument|InlineQueryResultGif|InlineQueryResultLocation|InlineQueryResultMpeg4Gif|InlineQueryResultPhoto|InlineQueryResultVenue|InlineQueryResultVideo|InlineQueryResultVoice $result,
        public ?bool $allowUserChats = null,
        public ?bool $allowBotChats = null,
        public ?bool $allowGroupChats = null,
        public ?bool $allowChannelChats = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SavePreparedInlineMessage;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'user_id'             => $this->userId,
                'result'              => $this->result->format(),
                'allow_user_chats'    => $this->allowUserChats,
                'allow_bot_chats'     => $this->allowBotChats,
                'allow_group_chats'   => $this->allowGroupChats,
                'allow_channel_chats' => $this->allowChannelChats,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
