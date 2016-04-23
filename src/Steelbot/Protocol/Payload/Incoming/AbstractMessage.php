<?php

namespace Steelbot\Protocol\Payload\Incoming;

use Steelbot\ClientInterface;
use Steelbot\GroupChatInterface;
use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\UserInterface;

/**
 * Class AbstractMessage
 *
 */
abstract class AbstractMessage implements IncomingPayloadInterface
{
    /**
     * @var ClientInterface
     */
    protected $from;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @param \Steelbot\ClientInterface $from
     * @param \Steelbot\ClientInterface $user
     */
    public function __construct(ClientInterface $from, UserInterface $user)
    {
        $this->from = $from;
        $this->user = $user;
    }

    /**
     * @return \Steelbot\ClientInterface
     */
    public function getFrom(): ClientInterface
    {
        return $this->from;
    }

    /**
     * @return \Steelbot\UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * @return bool|null
     */
    public function isGroupChatMessage()
    {
        if ($this->from->getId() != $this->user->getId()) {
            return true;
        } else {
            return false;
        }
    }

    abstract public function __toString(): string;
}
