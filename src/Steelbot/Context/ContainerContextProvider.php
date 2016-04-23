<?php

namespace Steelbot\Context;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Route\CallableRouteMatcher;
use Steelbot\Route\PcreRouteMatcher;
use Steelbot\Route\RouteMatcherInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class ContainerContextProvider
 * @package Steelbot\Context
 */
class ContainerContextProvider implements LoggerAwareInterface, ContainerAwareInterface, ContextProviderInterface
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
     * @param RouteMatcherInterface|string $regexp
     * @param string $handler
     */
    public function setRoute($matcher, string $serviceId): self
    {
        if (is_string($matcher)) {
            $matcher = new PcreRouteMatcher($matcher);
        } elseif (is_callable($matcher)) {
            $matcher = new CallableRouteMatcher($matcher);
        } elseif (!($matcher instanceof RouteMatcherInterface)) {
            throw new \DomainException("Matcher must implement RouteMatcherInterface or to be a string");
        }

        $this->routes[$serviceId] = $matcher;

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
    public function findContext(IncomingPayloadInterface $payload)
    {
        foreach ($this->routes as $serviceId => $routeMatcher) {
            $this->logger->debug("Checking route", ['class' => get_class($routeMatcher)]);

            if ($routeMatcher->match($payload)) {
                return $this->container->get($serviceId);
            }
        }

        return false;
    }
}
