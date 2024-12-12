<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Factory\FactoryInterface;
use Tests\Fixture\Benchmark\Factory\Response\Nested\MessageFactory;
use Tests\Fixture\Benchmark\Factory\Response\Nested\ResponseParametersFactory;
use Tests\Fixture\Benchmark\Model\Response\EditMessageReplyMarkupResponse;
use Tests\Fixture\Benchmark\Model\ResponseInterface;

final readonly class EditMessageReplyMarkupResponseFactory implements FactoryInterface
{
    public function __construct(
        private MessageFactory $messageFactory,
        private ResponseParametersFactory $responseParametersFactory,
    ) {
    }

    public function create(array $message): ResponseInterface
    {
        return new EditMessageReplyMarkupResponse(
            $message['ok'],
            match (true) {
                !isset($message['result'])                                 => null,
                is_bool($message['result']) && $message['result'] === true => $message['result'],
                default                                                    => $this->messageFactory->create(
                    $message['result'],
                )
            },
            $message['description'] ?? null,
            $message['error_code'] ?? null,
            isset($message['parameters']) ? $this->responseParametersFactory->create($message['parameters']) : null,
        );
    }

    public function supports(ApiMethod $method): bool
    {
        return $method === ApiMethod::EditMessageReplyMarkup;
    }
}
