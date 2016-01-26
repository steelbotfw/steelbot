<?php

namespace Steelbot\Context;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Steelbot\Application;
use Steelbot\ClientInterface;
use Steelbot\Protocol\AbstractProtocol;
use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Route\CallableRouteMatcher;
use Steelbot\Route\PcreRouteMatcher;
use Steelbot\Route\RouteMatcherInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContextProvider
 * @package Steelbot\Context
 */
class ContextProvider implements LoggerAwareInterface, ContainerAwareInterface
{
    use LoggerAwareTrait;
    use ContainerAwareTrait;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * ContextProvider constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * @param RouteMatcherInterface|string $regexp
     * @param string|callable $handler
     */
    public function setRoute($matcher, $handler): self
    {
        if (is_string($matcher)) {
            $matcher = new PcreRouteMatcher($matcher);
        } elseif (is_callable($matcher)) {
            $matcher = new CallableRouteMatcher($matcher);
        } elseif (!($matcher instanceof RouteMatcherInterface)) {
            throw new \DomainException("Matcher must implement RouteMatcherInterface or be a string");
        }

        $this->routes[] = [$matcher, $handler];
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
     * @return \Steelbot\Context\ContextInterface|false
     * @throws \Steelbot\Exception\ContextNotFoundException
     */
    public function findContext(IncomingPayloadInterface $payload, ClientInterface $client)
    {
        foreach ($this->routes as list($routeMatcher, $handler)) {
            $this->logger->debug("Checking route", ['class' => get_class($routeMatcher)]);

            if ($routeMatcher->match($payload)) {
                if (is_callable($handler)) {
                    $this->logger->debug("Returning callable handler");

                    return $handler;
                } elseif (class_exists($handler, true)) {
                    $this->logger->debug("Returning class handler");

                    return new $handler($this->container->get('protocol'), $client);
                } elseif (file_exists($handler)) {
                    $this->logger->debug("Returning anonymous class or closure", [
                        'file' => $handler
                    ]);

                    return $this->createContextFromFile($this->container->get('protocol'), $client, $handler);
                } else {
                    throw new \UnexpectedValueException("Error resolving context: $handler");
                }
            }
        }

        return false;
    }

    /**
     * @param \Steelbot\Application $app
     * @param \Steelbot\ClientInterface $client
     * @param string $filename
     *
     * @return \Steelbot\Context\ContextInterface|\Closure
     */
    protected function createContextFromFile(AbstractProtocol $protocol, ClientInterface $client, $filename)
    {
        return include $filename;
    }
}
