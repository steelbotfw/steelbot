<?php

namespace Steelbot\Protocol\Telegram\Message;

use Steelbot\ClientInterface;

/**
 * Class AbstractMessage
 *
 * @package Steelbot\Protocol\Telegram\Message
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

    /**
     * @return \Steelbot\ClientInterface
     */
    public function getFrom(): ClientInterface
    {
        return $this->from;
    }

    /**
     * @return \Steelbot\ClientInterface
     */
    public function getUser(): ClientInterface
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

    abstract public function __toString(): string;
}