<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\GetMyDefaultAdministratorRightsResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<GetMyDefaultAdministratorRightsResponse>
 */
final readonly class GetMyDefaultAdministratorRightsRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(public ?bool $forChannels = null)
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::GetMyDefaultAdministratorRights;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'for_channels' => $this->forChannels,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
