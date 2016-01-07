<?php

namespace Steelbot\Context;

use Steelbot\ClientInterface;
use Steelbot\Protocol\AbstractProtocol;
use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Protocol\Payload\Outgoing\TextMessage;

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
     * @param \Steelbot\Protocol\AbstractProtocol $protocol
     * @param \Steelbot\ClientInterface $client
     */
    public function __construct(AbstractProtocol $protocol, ClientInterface $client)
    {
        $this->protocol = $protocol;
        $this->client = $client;
    }

    /**
     * Handle incoming payload
     *
     * @param IncomingPayloadInterface $payload
     */
    abstract public function handle(IncomingPayloadInterface $payload);

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
