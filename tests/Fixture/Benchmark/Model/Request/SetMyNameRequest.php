<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Enum\Language;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetMyNameResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetMyNameResponse>
 */
final readonly class SetMyNameRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public ?string $name = null,
        public ?Language $languageCode = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetMyName;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'name'          => $this->name,
                'language_code' => $this->languageCode?->value ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
