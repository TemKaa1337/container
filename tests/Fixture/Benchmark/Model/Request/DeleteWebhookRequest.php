<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\DeleteWebhookResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<DeleteWebhookResponse>
 */
final readonly class DeleteWebhookRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(public ?bool $dropPendingUpdates = null)
    {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::DeleteWebhook;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'drop_pending_updates' => $this->dropPendingUpdates,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
