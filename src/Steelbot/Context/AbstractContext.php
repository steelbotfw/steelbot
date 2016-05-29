<?php

namespace Steelbot\Context;

use Steelbot\ClientInterface;
use Steelbot\Protocol\AbstractProtocol;
use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Protocol\Payload\Outgoing\TextMessage;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * Class AbstractContext
 * @package Steelbot\Context
 */
abstract class AbstractContext implements ContextInterface
{
    /**
     * @var \Steelbot\Protocol\AbstractProtocol
     */
    protected $protocol;

    /**
     * @var \Steelbot\ClientInterface
     */
    protected $client;

    /**
     * @var bool
     */
    protected $isResolved = false;

    /**
     * Handle incoming payload
     *
     * @param IncomingPayloadInterface $payload
     */
    abstract public function handle($payload);

    /**
     * @return bool
     */
    public function isResolved() : bool
    {
        return $this->isResolved;
    }

    /**
     * @return ClientInterface
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        if (!$this->client) {
            $this->client = $client;
        } else {
            throw new LogicException("Client already has been set");
        }

        return $this;
    }

    /**
     * @return AbstractProtocol
     */
    public function getProtocol(): AbstractProtocol
    {
        return $this->protocol;
    }

    /**
     * @param AbstractProtocol $protocol
     */
    public function setProtocol(AbstractProtocol $protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * Resolve current context
     *
     * @return $this
     */
    protected function resolve(): self
    {
        $this->isResolved = true;

        return $this;
    }

    /**
     * Send message to sender
     *
     * @param string $text
     * @param ...$args
     *
     * @return \Generator
     */
    protected function answer(string $text): \Generator
    {
        $textMessage = new TextMessage($text);

        return $this->protocol->send($this->client, $textMessage);
    }

}
