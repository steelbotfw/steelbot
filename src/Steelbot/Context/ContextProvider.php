<?php

namespace Steelbot\Context;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Route\CallableRouteMatcher;
use Steelbot\Route\PcreRouteMatcher;
use Steelbot\Route\RouteMatcherInterface;

/**
 * Class ContextProvider
 * @package Steelbot\Context
 */
class ContextProvider implements LoggerAwareInterface, ContextProviderInterface
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * @param RouteMatcherInterface|string $regexp
     * @param string|callable $handler
     */
    public function addRoute($matcher, $handler): self
    {
        if (is_string($matcher)) {
            $matcher = new PcreRouteMatcher($matcher);
        } elseif (is_callable($matcher)) {
            $matcher = new CallableRouteMatcher($matcher);
        } elseif (!($matcher instanceof RouteMatcherInterface)) {
            throw new \DomainException("Matcher must implement RouteMatcherInterface or be a string");
        }

        $this->routes[] = [$matcher, $handler];

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
        foreach ($this->routes as list($routeMatcher, $handler)) {
            $this->logger->debug("Checking route", ['class' => get_class($routeMatcher)]);

            if ($routeMatcher->match($payload)) {
                if (is_callable($handler)) {
                    $this->logger->debug("Returning callable handler");

                    return $handler;
                } elseif (class_exists($handler, true)) {
                    $this->logger->debug("Returning class handler");

                    return new $handler;
                } elseif (file_exists($handler)) {
                    $this->logger->debug("Returning anonymous class or closure", [
                        'file' => $handler
                    ]);

                    return $this->createContextFromFile($handler);
                } else {
                    throw new \UnexpectedValueException("Error resolving context: $handler");
                }
            }
        }

        return false;
    }

    /**
     * @param string $filename
     *
     * @return \Closure
     */
    protected function createContextFromFile(string $filename)
    {
        return require $filename;
    }
}
