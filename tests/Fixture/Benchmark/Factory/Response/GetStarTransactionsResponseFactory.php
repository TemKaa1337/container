<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Factory\FactoryInterface;
use Tests\Fixture\Benchmark\Factory\Response\Nested\ResponseParametersFactory;
use Tests\Fixture\Benchmark\Factory\Response\Nested\StarTransactionsFactory;
use Tests\Fixture\Benchmark\Model\Response\GetStarTransactionsResponse;
use Tests\Fixture\Benchmark\Model\ResponseInterface;

final readonly class GetStarTransactionsResponseFactory implements FactoryInterface
{
    public function __construct(
        private StarTransactionsFactory $starTransactionsFactory,
        private ResponseParametersFactory $responseParametersFactory,
    ) {
    }

    public function create(array $message): ResponseInterface
    {
        return new GetStarTransactionsResponse(
            $message['ok'],
            isset($message['result']) ? $this->starTransactionsFactory->create($message['result']) : null,
            $message['description'] ?? null,
            $message['error_code'] ?? null,
            isset($message['parameters']) ? $this->responseParametersFactory->create($message['parameters']) : null,
        );
    }

    public function supports(ApiMethod $method): bool
    {
        return $method === ApiMethod::GetStarTransactions;
    }
}
