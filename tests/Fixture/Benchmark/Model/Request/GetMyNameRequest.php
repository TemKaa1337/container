<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Enum\Language;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\GetMyNameResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<GetMyNameResponse>
 */
final readonly class GetMyNameRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(public ?Language $languageCode = null)
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::GetMyName;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'language_code' => $this->languageCode?->value ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
