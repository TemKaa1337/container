<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Enum\Language;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\SetMyCommandsResponse;
use Tests\Fixture\Benchmark\Model\Shared\BotCommand;
use Tests\Fixture\Benchmark\Model\Shared\BotCommandScopeAllChatAdministrators;
use Tests\Fixture\Benchmark\Model\Shared\BotCommandScopeAllGroupChats;
use Tests\Fixture\Benchmark\Model\Shared\BotCommandScopeAllPrivateChats;
use Tests\Fixture\Benchmark\Model\Shared\BotCommandScopeChat;
use Tests\Fixture\Benchmark\Model\Shared\BotCommandScopeChatAdministrators;
use Tests\Fixture\Benchmark\Model\Shared\BotCommandScopeChatMember;
use Tests\Fixture\Benchmark\Model\Shared\BotCommandScopeDefault;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<SetMyCommandsResponse>
 */
final readonly class SetMyCommandsRequest implements RequestInterface
{
    use ArrayFilterTrait;

    /**
     * @param BotCommand[] $commands
     */
    public function __construct(
        public array $commands,
        public BotCommandScopeDefault|BotCommandScopeAllPrivateChats|BotCommandScopeAllGroupChats|BotCommandScopeAllChatAdministrators|BotCommandScopeChat|BotCommandScopeChatAdministrators|BotCommandScopeChatMember|null $scope = null,
        public ?Language $languageCode = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::SetMyCommands;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'commands'      => array_map(
                    static fn (BotCommand $type): array => $type->format(),
                    $this->commands,
                ),
                'scope'         => $this->scope?->format() ?: null,
                'language_code' => $this->languageCode?->value ?: null,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
