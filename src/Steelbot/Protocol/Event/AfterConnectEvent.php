<?php

namespace Steelbot\Protocol\Event;

use Steelbot\Protocol\AbstractProtocol;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AfterConnectEvent
 * @package Steelbot\Protocol\Event
 */
class AfterConnectEvent extends Event
{
    const NAME = AbstractProtocol::EVENT_AFTER_CONNECT;

    /**
     * @var AbstractProtocol
     */
    protected $protocol;

    /**
     * AfterConnectEvent constructor.
     *
     * @param AbstractProtocol $protocol
     */
    public function __construct(AbstractProtocol $protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @return AbstractProtocol
     */
    public function getProtocol(): AbstractProtocol
    {
        return $this->protocol;
    }
}
