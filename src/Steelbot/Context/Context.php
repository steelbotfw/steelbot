<?php

namespace Steelbot\Context;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Steelbot\Application;
use Steelbot\ClientInterface;

class Context implements ContextInterface, LoggerAwareInterface
{
    /**
     * @var \Steelbot\Application
     */
    protected $app;

    /**
     * @var \Steelbot\ClientInterface
     */
    protected $client;

    /**
     * @var bool
     */
    protected $isResolved = false;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param \Steelbot\Application $app
     * @param \Steelbot\ClientInterface $client
     */
    public function __construct(Application $app, ClientInterface $client)
    {
        $this->app = $app;
        $this->client = $client;
    }

    /**
     * Handle context
     *
     * @param $message
     */
    public function handle($message) {
        echo "Recieved message: $message FROM ".$message->getClient()."\n";
        $this->resolve();
    }

    /**
     * @return bool
     */
    public function isResolved() : bool
    {
        return $this->isResolved;
    }

    /**
     * Resolve current context
     */
    protected function resolve()
    {
        $this->isResolved = true;
    }

    /**
     * @param string $text
     * @param ...$args
     *
     * @return mixed
     */
    protected function answer(string $text)
    {
        return $this->app->getProtocol()->send($this->client, $text);
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}