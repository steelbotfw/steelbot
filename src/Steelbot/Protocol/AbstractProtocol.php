<?php

namespace Steelbot\Protocol;
use Evenement\EventEmitterInterface;
use React\EventLoop\LoopInterface;
use Steelbot\ClientInterface;

/**
 * Interface ProtocolInterface
 * @package Steelbot\Protocol
 */
abstract class AbstractProtocol
{
    const EVENT_PRE_CONNECT        = 'protocol.preConnect';
    const EVENT_POST_CONNECT       = 'protocol.postConnect';
    const EVENT_PRE_DISCONNECT     = 'protocol.preDisconnect';
    const EVENT_POST_DISCONNECT    = 'protocol.postDisconnect';
    const EVENT_MESSAGE_RECEIVED   = 'protocol.message.received';
    const EVENT_MESSAGE_PRE_SEND   = 'protocol.message.preSend';
    const EVENT_MESSAGE_POST_SEND  = 'protocol.message.postSend';

    /**
     * @var LoopInterface
     */
    protected $loop;

    protected $eventEmitter;

    /**
     * @param $loop
     */
    public function __construct(LoopInterface $loop, EventEmitterInterface $eventEmitter) {
        $this->loop = $loop;
        $this->eventEmitter = $eventEmitter;
    }

    /**
     * @return boolean
     */
    abstract public function connect();

    /**
     * @return boolean
     */
    abstract public function disconnect();

    /**
     * @return boolean
     */
    abstract public function isConnected();

    /**
     * @param \Steelbot\ClientInterface $client
     * @param $text
     *
     * @return mixed
     */
    abstract public function send(ClientInterface $client, $text);

}