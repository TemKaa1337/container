<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Factory\Response;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Factory\FactoryInterface;
use Tests\Fixture\Benchmark\Factory\Response\Nested\ResponseParametersFactory;
use Tests\Fixture\Benchmark\Factory\Response\Nested\StickerFactory;
use Tests\Fixture\Benchmark\Model\Response\GetForumTopicIconStickersResponse;
use Tests\Fixture\Benchmark\Model\Response\Nested\Sticker;
use Tests\Fixture\Benchmark\Model\ResponseInterface;

final readonly class GetForumTopicIconStickersResponseFactory implements FactoryInterface
{
    public function __construct(
        private StickerFactory $stickerFactory,
        private ResponseParametersFactory $responseParametersFactory,
    ) {
    }

    public function create(array $message): ResponseInterface
    {
        return new GetForumTopicIconStickersResponse(
            $message['ok'],
            match (true) {
                isset($message['result']) => array_map(
                    fn (array $nested): Sticker => $this->stickerFactory->create($nested),
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
        return $method === ApiMethod::GetForumTopicIconStickers;
    }
}
