<?php

namespace Steelbot\Event;

use Steelbot\Protocol\IncomingPayloadInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class IncomingPayloadEvent
 * @package Steelbot\Event
 */
class IncomingPayloadEvent extends Event
{
    const NAME = self::class;

    /**
     * @var \Steelbot\Protocol\IncomingPayloadInterface
     */
    protected $payload;

    /**
     * IncomingPayloadEvent constructor.
     *
     * @param \Steelbot\Protocol\IncomingPayloadInterface $payload
     */
    public function __construct(IncomingPayloadInterface $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return \Steelbot\Protocol\IncomingPayloadInterface
     */
    public function getPayload(): IncomingPayloadInterface
    {
        return $this->payload;
    }
}
