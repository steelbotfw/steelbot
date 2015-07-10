<?php

namespace Steelbot;

use Evenement\EventEmitter;
use React\EventLoop\Factory;

class Application 
{
    /**
     * @var \SplObjectStorage
     */
    protected $modules;

    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var \Steelbot\Protocol\Telnet\Protocol
     */
    protected $protocol;

    /**
     * @var \Evenement\EventEmitterInterface
     */
    protected $eventEmitter;

    public function __construct($config)
    {
        $this->modules = new \SplObjectStorage();
        $this->eventEmitter = new EventEmitter();

        $this->loop = Factory::create();
        $this->loop->addPeriodicTimer(60, function () {
            echo '.';
        });

        echo "Loading protocol ... ";
        $protocolClass = $config['protocol'];
        $this->protocol = new $protocolClass($this->loop, $this->eventEmitter);
        echo "OK\n";

        foreach ($config['modules'] as $classname) {
            echo "Loading module $classname ... ";
            $this->modules->attach(new $classname($this));
            echo "OK\n";
        }
    }

    /**
     * @return \React\EventLoop\LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @return \Evenement\EventEmitterInterface
     */
    public function getEventEmitter()
    {
        return $this->eventEmitter;
    }

    /**
     * @param string $event
     * @param callable $listener
     */
    public function on($event, $listener)
    {
        $this->eventEmitter->on($event, $listener);
    }

    /**
     * @param \Steelbot\ClientInterface $client
     * @param $text
     */
    public function send(ClientInterface $client, $text)
    {
        $this->protocol->send($client, $text);
    }

    /**
     *
     */
    public function run()
    {
        echo "Steelbot 4.0-dev\n\n";
        $this->protocol->connect();

        $this->loop->run();
    }
} 
