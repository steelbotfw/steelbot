<?php

namespace Steelbot\Protocol;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Steelbot\ClientInterface;
use Steelbot\Ptorocol\OutgoingPayloadInterface;
use Steelbot\EventEmitter;

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
    const EVENT_PAYLOAD_RECEIVED   = 'protocol.payload.received';
    const EVENT_MESSAGE_PRE_SEND   = 'protocol.message.preSend';
    const EVENT_MESSAGE_POST_SEND  = 'protocol.message.postSend';

    /**
     * @var \Steelbot\EventEmitter
     */
    protected $eventEmitter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param $loop
     */
    public function __construct(EventEmitter $eventEmitter) {
        $this->eventEmitter = $eventEmitter;

        $this->eventEmitter
            ->addEvent(self::EVENT_PRE_CONNECT)
            ->addEvent(self::EVENT_POST_CONNECT)
            ->addEvent(self::EVENT_PRE_DISCONNECT)
            ->addEvent(self::EVENT_POST_DISCONNECT)
            ->addEvent(self::EVENT_PAYLOAD_RECEIVED)
            ->addEvent(self::EVENT_MESSAGE_PRE_SEND);
            // EVENT_MESSAGE_POST_SEND
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
     * @param mixed $payload
     *
     * @return mixed
     */
    abstract public function send(ClientInterface $client, $payload);
}