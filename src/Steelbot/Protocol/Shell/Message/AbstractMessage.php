<?php

namespace Steelbot\Protocol\Shell\Message;

use Steelbot\ClientInterface;

/**
 * Class AbstractMessage
 *
 * @package Steelbot\Protocol\Shell\Message
 */
abstract class AbstractMessage
{
    /**
     * @var ClientInterface
     */
    protected $from;

    /**
     * @var ClientInterface
     */
    protected $user;

    /**
     * @param \Steelbot\ClientInterface $from
     * @param \Steelbot\ClientInterface $user
     */
    public function __construct(ClientInterface $from, ClientInterface $user)
    {
        $this->from = $from;
        $this->user = $user;
    }

    public function getFrom() : ClientInterface
    {
        return $this->from;
    }

    public function getUser() : ClientInterface
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function isGroupChatMessage()
    {
        return $this->from->getId() !== $this->user->getId();
    }

    abstract public function __toString() : string;
}