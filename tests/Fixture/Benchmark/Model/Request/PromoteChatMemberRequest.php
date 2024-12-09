<?php

// THIS FILE IS GENERATED AUTOMATICALLY, DO NOT CHANGE IT MANUALLY

declare(strict_types=1);

namespace Tests\Fixture\Benchmark\Model\Request;

use Tests\Fixture\Benchmark\Enum\ApiMethod;
use Tests\Fixture\Benchmark\Enum\HttpMethod;
use Tests\Fixture\Benchmark\Model\RequestInterface;
use Tests\Fixture\Benchmark\Model\Response\PromoteChatMemberResponse;
use Tests\Fixture\Benchmark\Trait\ArrayFilterTrait;

/**
 * @api
 *
 * @implements RequestInterface<PromoteChatMemberResponse>
 */
final readonly class PromoteChatMemberRequest implements RequestInterface
{
    use ArrayFilterTrait;

    public function __construct(
        public int|string $chatId,
        public int $userId,
        public ?bool $isAnonymous = null,
        public ?bool $canManageChat = null,
        public ?bool $canDeleteMessages = null,
        public ?bool $canManageVideoChats = null,
        public ?bool $canRestrictMembers = null,
        public ?bool $canPromoteMembers = null,
        public ?bool $canChangeInfo = null,
        public ?bool $canInviteUsers = null,
        public ?bool $canPostStories = null,
        public ?bool $canEditStories = null,
        public ?bool $canDeleteStories = null,
        public ?bool $canPostMessages = null,
        public ?bool $canEditMessages = null,
        public ?bool $canPinMessages = null,
        public ?bool $canManageTopics = null,
    ) {
    }

    public function getApiMethod(): ApiMethod
    {
        return ApiMethod::PromoteChatMember;
    }

    public function getData(): array
    {
        return $this->filterNullable(
            [
                'chat_id'                => $this->chatId,
                'user_id'                => $this->userId,
                'is_anonymous'           => $this->isAnonymous,
                'can_manage_chat'        => $this->canManageChat,
                'can_delete_messages'    => $this->canDeleteMessages,
                'can_manage_video_chats' => $this->canManageVideoChats,
                'can_restrict_members'   => $this->canRestrictMembers,
                'can_promote_members'    => $this->canPromoteMembers,
                'can_change_info'        => $this->canChangeInfo,
                'can_invite_users'       => $this->canInviteUsers,
                'can_post_stories'       => $this->canPostStories,
                'can_edit_stories'       => $this->canEditStories,
                'can_delete_stories'     => $this->canDeleteStories,
                'can_post_messages'      => $this->canPostMessages,
                'can_edit_messages'      => $this->canEditMessages,
                'can_pin_messages'       => $this->canPinMessages,
                'can_manage_topics'      => $this->canManageTopics,
            ],
        );
    }

    public function getHttpMethod(): HttpMethod
    {
        return HttpMethod::Post;
    }
}
