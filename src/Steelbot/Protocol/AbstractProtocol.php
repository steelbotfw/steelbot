<?php

namespace Steelbot\Protocol;
use Evenement\EventEmitterInterface;
use React\EventLoop\LoopInterface;

/**
 * Interface ProtocolInterface
 * @package Steelbot\Protocol
 */
abstract class AbstractProtocol
{
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
}