<?php

namespace Steelbot\Protocol\Gitter;

use Icicle\Coroutine;
use Steelbot\ClientInterface;
use Steelbot\Protocol\OutgoingPayloadInterface;
use Steelbot\Protocol\Payload\Outgoing\TextMessage;

/**
 * @todo
 */
class Protocol extends \Steelbot\Protocol\AbstractProtocol
{
    /**
     * @var bool
     */
    private $isConnected = false;

    /**
     * @var
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param mixed $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret): string
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return boolean
     */
    public function connect(): \Generator
    {
        $this->logger->info("Connecting to server");

        $this->isConnected = true;
        $this->logger->info("Connected to server");
        $this->eventDispatcher->dispatch(self::EVENT_POST_CONNECT);

        while ($this->isConnected) {
            yield $this->processUpdates();
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function disconnect()
    {
        $this->eventDispatcher->dispatch(self::EVENT_PRE_DISCONNECT);
        $this->isConnected = false;
        $this->eventDispatcher->dispatch(self::EVENT_POST_DISCONNECT);

        return true;
    }

    /**
     * @return boolean
     */
    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    /**
     * @param \Steelbot\ClientInterface $client
     * @param OutgoingPayloadInterface|string $payload
     *
     * @return \Generator
     */
    public function send(ClientInterface $client, OutgoingPayloadInterface $payload): \Generator
    {
        if ($payload instanceof TextMessage) {

        }

        throw new \DomainException("Unknown payload type");
    }

    /**
     * Process updates from server
     *
     * @return \Generator
     */
    protected function processUpdates(): \Generator
    {
    }
}
