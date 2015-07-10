<?php

namespace Steelbot;

use Evenement\EventEmitter;
use React\EventLoop\Factory;

/**
 * Class Application
 * @package Steelbot
 */
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
     * @var \Steelbot\Protocol\AbstractProtocol
     */
    protected $protocol;

    /**
     * @var \Evenement\EventEmitterInterface
     */
    protected $eventEmitter;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->modules = new \SplObjectStorage();
        $this->eventEmitter = new EventEmitter();

        $this->loop = Factory::create();
        $this->loop->addPeriodicTimer(60, function () {
            echo '.';
        });

        echo "Loading protocol ... \n";
        $this->protocol = $this->instantiateProtocol($config['protocol']);

        if (isset($config['modules'])) {
            foreach ($config['modules'] as $module) {
                echo "Loading module $module ... ";
                $this->modules->attach($this->instantiateModule($module));
                echo "OK\n";
            }
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
     * @param string $text
     */
    public function send(ClientInterface $client, $text)
    {
        $this->protocol->send($client, $text);
    }

    /**
     * Start steelbot
     */
    public function run()
    {
        echo "Steelbot 4.0-dev\n\n";
        $this->protocol->connect();

        $this->loop->run();
    }

    /**
     * Stop steelbot
     */
    public function stop()
    {
        $this->loop->stop();
    }

    /**
     * @param string $protocol
     *
     * @return \Steelbot\Protocol\AbstractProtocol
     */
    protected function instantiateProtocol($protocol)
    {
        if (!class_exists($protocol)) {
            $protocol = 'Steelbot\\Protocol\\' . ucfirst($protocol) . '\\Protocol';
        }

        if (!class_exists($protocol)) {
            throw new \InvalidArgumentException("Unknown protocol class: $protocol");
        }

        return new $protocol($this->loop, $this->eventEmitter);
    }

    /**
     * @param string $module
     *
     * @return mixed
     */
    protected function instantiateModule($module)
    {
        if (!class_exists($module)) {
            $module = 'Steelbot\\Module\\' . ucfirst($module) . '\\Module';
        }

        if (!class_exists($module)) {
            throw new \InvalidArgumentException("Unknown module class: $module");
        }

        return new $module($this);
    }
} 
