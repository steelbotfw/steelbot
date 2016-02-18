<?php

namespace Steelbot\Protocol\Payload\Incoming;

use Steelbot\GroupChatInterface;
use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\UserInterface;

/**
 * Class AbstractMessage
 *
 */
class GroupChatNewParticipant implements IncomingPayloadInterface
{
    /**
     * @var GroupChatInterface
     */
    protected $groupChat;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @param \Steelbot\GroupChatInterface $from
     * @param \Steelbot\UserInterface $user
     */
    public function __construct(GroupChatInterface $groupChat, UserInterface $user)
    {
        $this->groupChat = $groupChat;
        $this->user = $user;
    }

    /**
     * @return GroupChatInterface
     */
    public function getGroupChat(): GroupChatInterface
    {
        return $this->groupChat;
    }

    /**
     * @return \Steelbot\UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function __toString(): string
    {
        $groupChatId = $this->groupChat->getId();
        $userId = $this->user->getId();

        return "chat:$groupChatId;user:$userId";
    }
}
