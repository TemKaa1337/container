<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\GetUpdatesResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<GetUpdatesResponse>
 */
final readonly class GetUpdatesRequest implements RequestInterface
{
    use ArrayFilterTrait;

    /**
     * @param string[]|null $allowedUpdates
     */
    public function __construct(
        public ?int $offset = null,
        public ?int $limit = null,
        public ?int $timeout = null,
        public ?array $allowedUpdates = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::GetUpdates;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'offset'          => $this->offset,
                'limit'           => $this->limit,
                'timeout'         => $this->timeout,
                'allowed_updates' => $this->allowedUpdates,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
