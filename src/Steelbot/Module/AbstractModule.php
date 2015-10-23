<?php

namespace Steelbot\Module;

use Steelbot\Application;

/**
 * Class Module
 * @package Steelbot\Module\Ping
 */
abstract class AbstractModule
{
    /**
     * @var \Steelbot\Application
     */
    protected $app;

    /**
     * @param \Steelbot\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function init()
    {

    }

    /**
     * @return string
     */
    abstract public function getId();
}