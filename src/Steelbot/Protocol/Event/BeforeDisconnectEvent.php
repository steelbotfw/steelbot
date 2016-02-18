<?php

namespace Steelbot\Protocol\Event;

use Steelbot\Protocol\AbstractProtocol;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BeforeDisconnectEvent
 * @package Steelbot\Protocol\Event
 */
class BeforeDisconnectEvent extends Event
{
    const NAME = AbstractProtocol::EVENT_BEFORE_DISCONNECT;

    /**
     * @var AbstractProtocol
     */
    protected $protocol;

    /**
     * BeforeDisconnectEvent constructor.
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
