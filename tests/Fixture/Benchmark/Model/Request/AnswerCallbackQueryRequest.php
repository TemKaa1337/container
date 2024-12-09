<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\AnswerCallbackQueryResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<AnswerCallbackQueryResponse>
 */
final readonly class AnswerCallbackQueryRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public string $callbackQueryId,
        public ?string $text = null,
        public ?bool $showAlert = null,
        public ?string $url = null,
        public ?int $cacheTime = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::AnswerCallbackQuery;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'callback_query_id' => $this->callbackQueryId,
                'text'              => $this->text,
                'show_alert'        => $this->showAlert,
                'url'               => $this->url,
                'cache_time'        => $this->cacheTime,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
