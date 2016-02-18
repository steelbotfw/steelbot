<?php

namespace Steelbot\Protocol;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Steelbot\ClientInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface ProtocolInterface
 * @package Steelbot\Protocol
 */
abstract class AbstractProtocol implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const EVENT_BEFORE_CONNECT        = 'protocol.connect.before';
    const EVENT_AFTER_CONNECT       = 'protocol.connect.after';
    const EVENT_BEFORE_DISCONNECT     = 'protocol.disconnect.before';
    const EVENT_AFTER_DISCONNECT    = 'protocol.disconnect.after';
    const EVENT_MESSAGE_PRE_SEND   = 'protocol.message.preSend';
    const EVENT_MESSAGE_POST_SEND  = 'protocol.message.postSend';

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param $loop
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return bool
     */
    abstract public function connect();

    /**
     * @return mixed
     */
    abstract public function processUpdates();

    /**
     * @return boolean
     */
    abstract public function disconnect();

    /**
     * @return boolean
     */
    abstract public function isConnected(): bool;

    /**
     * @param \Steelbot\ClientInterface $client
     * @param OutgoingPayloadInterface $payload
     *
     * @return mixed
     */
    abstract public function send(ClientInterface $client, OutgoingPayloadInterface $payload): \Generator;
}
