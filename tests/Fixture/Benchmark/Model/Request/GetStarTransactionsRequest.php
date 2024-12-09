<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\GetStarTransactionsResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<GetStarTransactionsResponse>
 */
final readonly class GetStarTransactionsRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public ?int $offset = null,
        public ?int $limit = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::GetStarTransactions;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'offset' => $this->offset,
                'limit'  => $this->limit,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
