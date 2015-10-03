<?php

namespace Steelbot;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Steelbot\Context\ContextInterface;
use Steelbot\Context\IncludeFileContext;
use Steelbot\Context\PcreRouteMatcher;
use Steelbot\Exception\ContextNotFoundException;
use Steelbot\Protocol\TextMessageInterface;

/**
 * Class ContextRouter
 *
 * @package Steelbot
 */
class ContextRouter implements LoggerAwareInterface
{
    protected $app;

    /**
     * @var array
     */
    protected $routes;

    /**
     * @var ContextInterface[]
     */
    protected $clientContexts = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param \Steelbot\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->routes = new \SplObjectStorage();
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param mixed $payload
     *
     * @return null|1|ContextInterface
     */
    public function handle($payload)
    {
        $client = $payload->getFrom();
        $clientId = $client->getId();

        $this->logger->debug("New payload from $clientId");

        if (isset($this->clientContexts[$clientId])) {
            $context = $this->clientContexts[$clientId];
        } else {
            $context = $this->findContext($payload, $client);

            if ($context === null) {
                throw new ContextNotFoundException;
            }

            $this->logger->debug("Assigning context ".get_class($context)." for $clientId");

            $this->clientContexts[$clientId] = $context;
        }

        yield $context->handle($payload);

        if ($this->clientContexts[$clientId]->isResolved()) {
            $this->logger->debug("Destroying context for $clientId");
            unset($context);
            unset($this->clientContexts[$clientId]);
        }

        return true;
    }

    /**
     * @param string $regexp
     * @param string $contextClass
     */
    public function setRoute($matcher, string $contextClass)
    {
        if (is_string($matcher)) {
            $matcher = new PcreRouteMatcher($matcher);
        }

        $this->routes[$matcher] = $contextClass;

        return $this;
    }

    /**
     * @param \Steelbot\string $text
     * @param \Steelbot\ClientInterface $client
     *
     * @return null|ContextInterface
     */
    protected function findContext($payload, ClientInterface $client)
    {
        foreach ($this->routes as $routeMatcher) {
            $this->logger->debug("Checking route", []);

            if ($routeMatcher->match($payload)) {
                $handlerString = $this->routes[$routeMatcher];
                if (class_exists($handlerString, true)) {
                    return new $handlerString($this->app, $client);
                } elseif (file_exists($handlerString)) {
                    return new IncludeFileContext($this->app, $client, $handlerString);
                } else {
                    throw new \UnexpectedValueException("Error resolving context: $handlerString");
                }
            }
        }

        return null;
    }
}