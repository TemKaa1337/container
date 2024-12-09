<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\AnswerWebAppQueryResponse;
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

/**
 * @api
 *
 * @implements RequestInterface<AnswerWebAppQueryResponse>
 */
final readonly class AnswerWebAppQueryRequest implements RequestInterface
{
    public function __construct(
        public string $webAppQueryId,
        public InlineQueryResultCachedAudio|InlineQueryResultCachedDocument|InlineQueryResultCachedGif|InlineQueryResultCachedMpeg4Gif|InlineQueryResultCachedPhoto|InlineQueryResultCachedSticker|InlineQueryResultCachedVideo|InlineQueryResultCachedVoice|InlineQueryResultArticle|InlineQueryResultAudio|InlineQueryResultContact|InlineQueryResultGame|InlineQueryResultDocument|InlineQueryResultGif|InlineQueryResultLocation|InlineQueryResultMpeg4Gif|InlineQueryResultPhoto|InlineQueryResultVenue|InlineQueryResultVideo|InlineQueryResultVoice $result,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::AnswerWebAppQuery;
    }

    public function getData(): array
    {
        return [
            'web_app_query_id' => $this->webAppQueryId,
            'result'           => $this->result->format(),
        ];
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
