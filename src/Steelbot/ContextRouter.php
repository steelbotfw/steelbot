<?php

namespace Steelbot;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Steelbot\Context\Context;
use Steelbot\Context\ContextInterface;
use Steelbot\Context\IncludeFileContext;
use Steelbot\Context\PcreRouteMatcher;
use Steelbot\Exception\ContextNotFoundException;
use Steelbot\Protocol\TextMessageInterface;
use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Context\RouteMatcherInterface;
use Steelbot\Protocol\Telegram\HelpContext;

/**
 * Class ContextRouter
 *
 * @package Steelbot
 */
class ContextRouter implements LoggerAwareInterface
{
    /**
     * @var \Steelbot\Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $routes = [];

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

        $helpMatcher = new PcreRouteMatcher('~^/help$~i');
        $helpMatcher->setGroupChat(true);
        $this->setRoute($helpMatcher, HelpContext::class);
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param IncomingPayloadInterface $payload
     *
     * @return \Generator
     */
    public function handle(IncomingPayloadInterface $payload): \Generator
    {
        $client = $payload->getFrom();
        $clientId = $client->getId();

        $this->logger->debug("New payload", ['clientId' => $clientId]);

        if (isset($this->clientContexts[$clientId])) {
            $context = $this->clientContexts[$clientId];
        } else {
            $context = $this->findContext($payload, $client);
            if ($context instanceof LoggerAwareInterface) {
                $context->setLogger($this->logger);
            }
            $this->logger->debug("Assigning context", [
                'class' => get_class($context),
                'clientId' => $clientId
            ]);
            $this->clientContexts[$clientId] = $context;
        }

        if (is_callable($context))  {
            yield $context($payload, $this->app, $client);
            $isResolved = true;

        } elseif ($context instanceof ContextInterface) {
            yield $context->handle($payload);
            $isResolved = $context->isResolved();
        }

        if ($isResolved) {
            $this->logger->debug("Destroying context", ['clientId' => $clientId]);
            unset($context);
            unset($this->clientContexts[$clientId]);
        }

        return true;
    }

    /**
     * @param RouteMatcherInterface|string $regexp
     * @param string|callable $handlerString
     */
    public function setRoute($matcher, $handler, array $help = []): self
    {
        if (is_string($matcher)) {
            $matcher = new PcreRouteMatcher($matcher);
            $matcher->setHelp($help);
        }

        $this->routes[$matcher->getPriority()][] = [$matcher, $handler];
        ksort($this->routes);

        return $this;
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param \Steelbot\Protocol\IncomingPayloadInterface $payload
     * @param \Steelbot\ClientInterface $client
     *
     * @return \Steelbot\Context\ContextInterface
     * @throws \Steelbot\Exception\ContextNotFoundException
     */
    protected function findContext(IncomingPayloadInterface $payload, ClientInterface $client)
    {
        foreach ($this->routes as $priority => $pairs) {
            $this->logger->debug("Checking route priority", ['priority' => $priority]);

            foreach ($pairs as $pair) {
                list($routeMatcher, $handler) = $pair;

                if ($routeMatcher->match($payload)) {
                    if (is_callable($handler)) {
                        $this->logger->debug("Returning callable handler");
                        return $handler;
                    } elseif (class_exists($handler, true)) {
                        $this->logger->debug("Returning class handler");
                        return new $handler($this->app, $client);
                    } elseif (file_exists($handler)) {
                        $this->logger->debug("Returning anonymous class or closure", [
                            'file' => $handler
                        ]);
                        return $this->createContextFromFile($this->app, $client, $handler);
                    } else {
                        throw new \UnexpectedValueException("Error resolving context: $handler");
                    }
                }
            }
        }

        throw new ContextNotFoundException;
    }

    /**
     * @param \Steelbot\Application $app
     * @param \Steelbot\ClientInterface $client
     * @param string $filename
     *
     * @return \Steelbot\Context\ContextInterface|\Closure
     */
    protected function createContextFromFile(Application $app, ClientInterface $client, $filename)
    {
        return include $filename;
    }
}