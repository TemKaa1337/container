<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response;

use InvalidArgumentException;
use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Factory\FactoryInterface;
use Tests\Fixture\Benchmark\Factory\Response\Nested\ResponseParametersFactory;
use Tests\Fixture\Benchmark\Factory\Shared\MenuButtonCommandsFactory;
use Tests\Fixture\Benchmark\Factory\Shared\MenuButtonDefaultFactory;
use Tests\Fixture\Benchmark\Factory\Shared\MenuButtonWebAppFactory;
use Tests\Fixture\Benchmark\Model\Response\GetChatMenuButtonResponse;
use Tests\Fixture\Benchmark\Model\ResponseInterface;

final readonly class GetChatMenuButtonResponseFactory implements FactoryInterface
{
    public function __construct(
        private MenuButtonCommandsFactory $menuButtonCommandsFactory,
        private MenuButtonWebAppFactory $menuButtonWebAppFactory,
        private MenuButtonDefaultFactory $menuButtonDefaultFactory,
        private ResponseParametersFactory $responseParametersFactory,
    ) {
    }

    public function create(array $message): ResponseInterface
    {
        return new GetChatMenuButtonResponse(
            $message['ok'],
            match (true) {
                !isset($message['result']) => null,
                $message['result']['type'] === 'commands' => $this->menuButtonCommandsFactory->create(
                    $message['result'],
                ),
                $message['result']['type'] === 'web_app' => $this->menuButtonWebAppFactory->create($message['result']),
                $message['result']['type'] === 'default' => $this->menuButtonDefaultFactory->create(
                    $message['result'],
                ),
                default => throw new InvalidArgumentException(
                    'Could not find factory for message.',
                )
            },
            $message['description'] ?? null,
            $message['error_code'] ?? null,
            isset($message['parameters']) ? $this->responseParametersFactory->create($message['parameters']) : null,
        );
    }

    public function supports(ApiMethod $method): bool
    {
        return $method === ApiMethod::GetChatMenuButton;
    }
}
