<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetChatMenuButtonResponse;
use Tests\Fixture\Benchmark\Model\Shared\MenuButtonCommands;
use Tests\Fixture\Benchmark\Model\Shared\MenuButtonDefault;
use Tests\Fixture\Benchmark\Model\Shared\MenuButtonWebApp;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetChatMenuButtonResponse>
 */
final readonly class SetChatMenuButtonRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public ?int $chatId = null,
        public MenuButtonCommands|MenuButtonWebApp|MenuButtonDefault|null $menuButton = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetChatMenuButton;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'     => $this->chatId,
                'menu_button' => $this->menuButton?->format() ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
