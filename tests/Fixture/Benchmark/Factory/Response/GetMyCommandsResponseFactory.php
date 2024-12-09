<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Factory\FactoryInterface;
use Tests\Fixture\Benchmark\Factory\Response\Nested\ResponseParametersFactory;
use Tests\Fixture\Benchmark\Factory\Shared\BotCommandFactory;
use Tests\Fixture\Benchmark\Model\Response\GetMyCommandsResponse;
use Tests\Fixture\Benchmark\Model\ResponseInterface;
use Tests\Fixture\Benchmark\Model\Shared\BotCommand;

final readonly class GetMyCommandsResponseFactory implements FactoryInterface
{
    public function __construct(
        private BotCommandFactory $botCommandFactory,
        private ResponseParametersFactory $responseParametersFactory,
    ) {
    }

    public function create(array $message): ResponseInterface
    {
        return new GetMyCommandsResponse(
            $message['ok'],
            match (true) {
                isset($message['result']) => array_map(
                    fn (array $nested): BotCommand => $this->botCommandFactory->create($nested),
                    $message['result'],
                ),
                default                   => null,
            },
            $message['description'] ?? null,
            $message['error_code'] ?? null,
            isset($message['parameters']) ? $this->responseParametersFactory->create($message['parameters']) : null,
        );
    }

    public function supports(ApiMethod $method): bool
    {
        return $method === ApiMethod::GetMyCommands;
    }
}
