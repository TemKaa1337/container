<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Factory\FactoryInterface;
use Tests\Fixture\Benchmark\Factory\Response\Nested\ResponseParametersFactory;
use Tests\Fixture\Benchmark\Factory\Response\Nested\UserChatBoostsFactory;
use Tests\Fixture\Benchmark\Model\Response\GetUserChatBoostsResponse;
use Tests\Fixture\Benchmark\Model\ResponseInterface;

final readonly class GetUserChatBoostsResponseFactory implements FactoryInterface
{
    public function __construct(
        private UserChatBoostsFactory $userChatBoostsFactory,
        private ResponseParametersFactory $responseParametersFactory,
    ) {
    }

    public function create(array $message): ResponseInterface
    {
        return new GetUserChatBoostsResponse(
            $message['ok'],
            isset($message['result']) ? $this->userChatBoostsFactory->create($message['result']) : null,
            $message['description'] ?? null,
            $message['error_code'] ?? null,
            isset($message['parameters']) ? $this->responseParametersFactory->create($message['parameters']) : null,
        );
    }

    public function supports(ApiMethod $method): bool
    {
        return $method === ApiMethod::GetUserChatBoosts;
    }
}
