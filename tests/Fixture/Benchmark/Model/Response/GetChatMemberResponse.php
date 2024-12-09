<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Response;

use Tests\Fixture\Benchmark\Model\Response\Nested\ChatMemberAdministrator;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatMemberBanned;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatMemberLeft;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatMemberMember;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatMemberOwner;
use Tests\Fixture\Benchmark\Model\Response\Nested\ChatMemberRestricted;
use Tests\Fixture\Benchmark\Model\Response\Nested\ResponseParameters;
use Tests\Fixture\Benchmark\Model\ResponseInterface;

final readonly class GetChatMemberResponse implements ResponseInterface
{
    public function __construct(
        public bool $ok,
        public ChatMemberOwner|ChatMemberAdministrator|ChatMemberMember|ChatMemberRestricted|ChatMemberLeft|ChatMemberBanned|null $result = null,
        public ?string $description = null,
        public ?int $errorCode = null,
        public ?ResponseParameters $parameters = null,
    ) {
    }
}
