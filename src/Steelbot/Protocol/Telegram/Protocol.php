<?php

namespace Steelbot\Protocol\Telegram;

use Icicle\Coroutine;
use Steelbot\ClientInterface;
use Steelbot\Protocol\Event\IncomingPayloadEvent;
use Steelbot\Protocol\OutgoingPayloadInterface;
use Steelbot\Protocol\Payload\Outgoing\ImageMessage;
use Steelbot\Protocol\Payload\Outgoing\TextMessage;

/**
 * Class Protocol
 * @package Steelbot\Protocol\Telegram
 */
class Protocol extends \Steelbot\Protocol\AbstractProtocol
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var int
     */
    protected $lastUpdateId = 1;

    /**
     * @var string Telegram bot token
     */
    private $token;

    /**
     * @var bool
     */
    private $isConnected = false;

    /**
     * @param string $token
     */
    public function setToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @return boolean
     */
    public function connect(): \Generator
    {
        $this->api = new Api($this->token, $this->logger);
        $this->logger->info("Connecting to server");
        $user = yield from $this->api->getMe();

        $this->logger->info("Bot identified as @{$user->username}, {$user->firstName} {$user->lastName}, ID {$user->id}");

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
        unset($this->api);
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
    public function send(ClientInterface $client, OutgoingPayloadInterface $payload, $replyMarkup = null): \Generator
    {
        if ($payload instanceof TextMessage) {
            return $this->api->sendMessage($client->getId(), $payload->getText(), 'Markdown', false, null, $replyMarkup);
        } elseif ($payload instanceof ImageMessage) {
            return $this->api->sendPhoto($client->getId(), $payload->getResource(), null, null, null);
        }

        throw new \DomainException("Unknown payload type");
    }

    /**
     * @return Api
     */
    public function getApi(): Api
    {
        return $this->api;
    }

    /**
     * Process updates from server
     *
     * @return \Generator
     */
    protected function processUpdates(): \Generator
    {
        try {
            $updates = yield from $this->api->getUpdates($this->lastUpdateId);

            foreach ($updates as $update) {
                $incomingPayload = new IncomingPayload($update);

                try {
                    $message = $incomingPayload->getMessage();

                    $this->eventDispatcher->dispatch(IncomingPayloadEvent::NAME, new IncomingPayloadEvent($message));
                } catch (\DomainException $e) {
                    $this->logger->error($e->getMessage());
                }

                $this->lastUpdateId = $update->updateId;
            }
        } catch (\Icicle\Socket\Exception\Exception $exception) {
            $this->logger->warning($exception->getMessage());
        }// @todo catch NotOk exception

        return  true;
    }
}
