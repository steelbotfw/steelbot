<?php

namespace Steelbot;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Steelbot\Context\{ContextInterface, ContextProvider};
use Steelbot\Exception\ContextNotFoundException;
use Steelbot\Protocol\IncomingPayloadInterface;

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
     * @var ContextProvider[]
     */
    protected $contextProviders = [];

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
    public function __construct($container)
    {
        $this->app = $container->get('kernel');
    }

    /**
     * @param \Steelbot\Context\ContextProvider $contextProvider
     *
     * @return ContextRouter
     */
    public function addContextProvider(ContextProvider $contextProvider): self
    {
        $this->contextProviders[] = $contextProvider;

        return $this;
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
     * @param \Steelbot\Protocol\IncomingPayloadInterface $payload
     * @param \Steelbot\ClientInterface $client
     *
     * @return \Steelbot\Context\ContextInterface
     * @throws \Steelbot\Exception\ContextNotFoundException
     */
    protected function findContext(IncomingPayloadInterface $payload, ClientInterface $client)
    {
        foreach ($this->contextProviders as $contextProvider) {
            $this->logger->debug("Checking provider", ['class' => get_class($contextProvider)]);

            if ($context = $contextProvider->findContext($payload, $client, $this->app)) {
                return $context;
            }
        }

        throw new ContextNotFoundException;
    }
}
