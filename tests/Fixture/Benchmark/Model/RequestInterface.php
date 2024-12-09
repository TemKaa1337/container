<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;

/**
 * @api
 *
 * @template TResponse of ResponseInterface
 */
interface RequestInterface
{
    public function getApiMethod(): ApiMethod;

    /**
     * @return array<string, mixed>
     */
    public function getData(): array;

    public function getHttpMethod(): HttpMethod;
}
