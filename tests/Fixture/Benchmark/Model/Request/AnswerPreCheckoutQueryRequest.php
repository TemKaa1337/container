<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\AnswerPreCheckoutQueryResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<AnswerPreCheckoutQueryResponse>
 */
final readonly class AnswerPreCheckoutQueryRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public string $preCheckoutQueryId,
        public bool $ok,
        public ?string $errorMessage = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::AnswerPreCheckoutQuery;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'pre_checkout_query_id' => $this->preCheckoutQueryId,
                'ok'                    => $this->ok,
                'error_message'         => $this->errorMessage,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
