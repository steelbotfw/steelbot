<?php

namespace Steelbot\Protocol;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Steelbot\ClientInterface;
use Steelbot\Protocol\OutgoingPayloadInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface ProtocolInterface
 * @package Steelbot\Protocol
 */
abstract class AbstractProtocol implements LoggerAwareInterface
{
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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param $loop
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
