<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Factory\FactoryInterface;
use Tests\Fixture\Benchmark\Factory\Response\Nested\MessageIdFactory;
use Tests\Fixture\Benchmark\Factory\Response\Nested\ResponseParametersFactory;
use Tests\Fixture\Benchmark\Model\Response\ForwardMessagesResponse;
use Tests\Fixture\Benchmark\Model\Response\Nested\MessageId;
use Tests\Fixture\Benchmark\Model\ResponseInterface;

final readonly class ForwardMessagesResponseFactory implements FactoryInterface
{
    public function __construct(
        private MessageIdFactory $messageIdFactory,
        private ResponseParametersFactory $responseParametersFactory,
    ) {
    }

    public function create(array $message): ResponseInterface
    {
        return new ForwardMessagesResponse(
            $message['ok'],
            match (true) {
                isset($message['result']) => array_map(
                    fn (array $nested): MessageId => $this->messageIdFactory->create($nested),
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
        return $method === ApiMethod::ForwardMessages;
    }
}
