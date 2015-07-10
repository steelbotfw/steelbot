<?php

namespace Steelbot\Module\Ping;

use Steelbot\Application;
use Steelbot\Message;
use Steelbot\Module\AbstractModule;

/**
 * Class Module
 * @package Steelbot\Module\Ping
 */
class Module extends AbstractModule
{
    /**
     * @param \Steelbot\Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $app->on('message', [$this, 'incomingMessage']);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return 'steelbot.module.ping';
    }

    /**
     * @param \Steelbot\Message $message
     */
    public function incomingMessage(Message $message)
    {
        if (strcasecmp($message, 'ping')===0) {
            $this->app->send($message->getClient(), 'PONG');
        }
    }
} 