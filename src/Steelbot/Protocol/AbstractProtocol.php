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

    const EVENT_PRE_CONNECT        = 'protocol.preConnect';
    const EVENT_POST_CONNECT       = 'protocol.postConnect';
    const EVENT_PRE_DISCONNECT     = 'protocol.preDisconnect';
    const EVENT_POST_DISCONNECT    = 'protocol.postDisconnect';
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
