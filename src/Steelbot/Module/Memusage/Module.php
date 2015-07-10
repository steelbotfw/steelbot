<?php

namespace Steelbot\Module\Memusage;
use Steelbot\Application;
use Steelbot\Module\AbstractModule;

/**
 * Class Module
 * @package Steelbot\Module\Memusage
 */
class Module extends AbstractModule
{
    const PERIOD = 5;

    /**
     * @param \Steelbot\Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $app->getLoop()->addPeriodicTimer(self::PERIOD, [$this, 'onTimer']);
    }

    public function getId()
    {
        return 'steelbot.module.memusage';
    }

    /**
     * @param \Steelbot\Message $message
     */
    public function onTimer()
    {
        $memory = memory_get_usage() / 1024;
        $formatted = number_format($memory, 3).'K';
        echo "Current memory usage: {$formatted}\n";
    }
}