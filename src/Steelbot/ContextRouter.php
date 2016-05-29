<?php

namespace Steelbot;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Steelbot\Context\{
    ContextInterface, ContextProviderInterface
};
use Steelbot\Exception\ContextNotFoundException;
use Steelbot\Protocol\AbstractProtocol;
use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Protocol\Payload\Incoming\AbstractMessage;

/**
 * Class ContextRouter
 *
 * @package Steelbot
 */
class ContextRouter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ContextProvider[]
     */
    protected $contextProviders = [];

    /**
     * @var ContextInterface[]
     */
    protected $clientContexts = [];

    /**
     * @param \Steelbot\Context\ContextProvider $contextProvider
     *
     * @return ContextRouter
     */
    public function addContextProvider(ContextProviderInterface $contextProvider): self
    {
        $this->contextProviders[] = $contextProvider;

        return $this;
    }

    /**
     * @return ContextProviderInterface[]
     */
    public function getContextProviders(): array
    {
        return $this->contextProviders;
    }

    /**
     * @param AbstractMessage $message
     *
     * @return \Generator
     */
    public function handle(AbstractMessage $message, AbstractProtocol $protocol): \Generator
    {
        $client = $message->getFrom();
        $clientId = $client->getId();

        $this->logger->debug("New payload", ['clientId' => $clientId]);

        $context = $this->clientContexts[$clientId] ?? $this->buildContext($message, $protocol);

        if (is_callable($context))  {
            yield $context($message, $client);
            $isResolved = true;

        } elseif ($context instanceof ContextInterface) {
            yield $context->handle($message);
            $isResolved = $context->isResolved();
        }

        if ($isResolved) {
            $this->terminateContext($clientId);
        }

        return true;
    }

    /**
     * @param AbstractMessage  $message
     * @param AbstractProtocol $protocol
     *
     * @return ContextInterface
     * @throws ContextNotFoundException
     */
    protected function buildContext(AbstractMessage $message, AbstractProtocol $protocol): ContextInterface
    {
        $context = $this->findContext($message);
        $context->setClient($message->getFrom());
        $context->setProtocol($protocol);

        if ($context instanceof LoggerAwareInterface) {
            $context->setLogger($this->logger);
        }
        $this->logger->debug("Assigning context", [
            'class' => get_class($context),
            'clientId' => $message->getFrom()->getId()
        ]);

        $this->clientContexts[$message->getFrom()->getId()] = $context;

        return $context;
    }

    /**
     * @param \Steelbot\Protocol\IncomingPayloadInterface $payload
     * @param \Steelbot\ClientInterface $client
     *
     * @return \Steelbot\Context\ContextInterface
     * @throws \Steelbot\Exception\ContextNotFoundException
     */
    protected function findContext(IncomingPayloadInterface $payload)
    {
        foreach ($this->contextProviders as $contextProvider) {
            $this->logger->debug("Checking provider", ['class' => get_class($contextProvider)]);

            $context = $contextProvider->findContext($payload);
            if ($context !== false) {
                return $context;
            }
        }

        throw new ContextNotFoundException;
    }

    /**
     * @param $clientId
     */
    protected function terminateContext($clientId)
    {
        if (!isset($this->clientContexts[$clientId])) {
            throw new \UnexpectedValueException("There is no context for client");
        }
        $this->logger->debug("Destroying context", ['clientId' => $clientId]);
        unset($this->clientContexts[$clientId]);
    }
}
