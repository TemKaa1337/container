<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\AnswerInlineQueryResponse;
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
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultsButton;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultVenue;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultVideo;
use Tests\Fixture\Benchmark\Model\Shared\InlineQueryResultVoice;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<AnswerInlineQueryResponse>
 */
final readonly class AnswerInlineQueryRequest implements RequestInterface
{
    use ArrayFilterTrait;

    /**
     * @param InlineQueryResultCachedAudio[]|InlineQueryResultCachedDocument[]|InlineQueryResultCachedGif[]|InlineQueryResultCachedMpeg4Gif[]|InlineQueryResultCachedPhoto[]|InlineQueryResultCachedSticker[]|InlineQueryResultCachedVideo[]|InlineQueryResultCachedVoice[]|InlineQueryResultArticle[]|InlineQueryResultAudio[]|InlineQueryResultContact[]|InlineQueryResultGame[]|InlineQueryResultDocument[]|InlineQueryResultGif[]|InlineQueryResultLocation[]|InlineQueryResultMpeg4Gif[]|InlineQueryResultPhoto[]|InlineQueryResultVenue[]|InlineQueryResultVideo[]|InlineQueryResultVoice[] $results
     */
    public function __construct(
        public string $inlineQueryId,
        public array $results,
        public ?int $cacheTime = null,
        public ?bool $isPersonal = null,
        public ?string $nextOffset = null,
        public ?InlineQueryResultsButton $button = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::AnswerInlineQuery;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'inline_query_id' => $this->inlineQueryId,
                'results'         => array_map(
                    static fn (
                        InlineQueryResultCachedAudio|InlineQueryResultCachedDocument|InlineQueryResultCachedGif|InlineQueryResultCachedMpeg4Gif|InlineQueryResultCachedPhoto|InlineQueryResultCachedSticker|InlineQueryResultCachedVideo|InlineQueryResultCachedVoice|InlineQueryResultArticle|InlineQueryResultAudio|InlineQueryResultContact|InlineQueryResultGame|InlineQueryResultDocument|InlineQueryResultGif|InlineQueryResultLocation|InlineQueryResultMpeg4Gif|InlineQueryResultPhoto|InlineQueryResultVenue|InlineQueryResultVideo|InlineQueryResultVoice $type,
                    ): array => $type->format(),
                    $this->results,
                ),
                'cache_time'      => $this->cacheTime,
                'is_personal'     => $this->isPersonal,
                'next_offset'     => $this->nextOffset,
                'button'          => $this->button?->format() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
