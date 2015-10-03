<?php

namespace Steelbot\Protocol\Telegram;

use Icicle\Promise\Exception\TimeoutException;
use Icicle\Coroutine;

use Steelbot\ClientInterface;

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
    public function connect()
    {
        $this->logger->info("Connecting to server");

        $this->api = new Api($this->token, $this->logger);

        $user = yield $this->api->getMe();

        $this->logger->info("Bot identified as @{$user->username}, {$user->firstName}, ID {$user->id}");

        $this->isConnected = true;
        $this->logger->info("Connected to server");
        $this->eventEmitter->emit(self::EVENT_POST_CONNECT);

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
        $this->eventEmitter->emit(self::EVENT_PRE_DISCONNECT);
        $this->isConnected = false;
        unset($this->api);
        $this->eventEmitter->emit(self::EVENT_POST_DISCONNECT);

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
     * @return mixed
     */
    public function send(ClientInterface $client, $payload, $replyMarkup = null)
    {
        if (is_string($payload)) {
            return $this->api->sendMessage($client->getId(), $payload, false, null, $replyMarkup);
        }

        throw new \DomainException("Unknown payload type");
    }

    public function getApi()
    {
        return $this->api;
    }

    /**
     * Process updates from server
     */
    protected function processUpdates()
    {
        try {
            $updates = yield $this->api->getUpdates($this->lastUpdateId);

            foreach ($updates as $update) {
                $incomingPayload = new IncomingPayload($update);

                try {
                    $message = $incomingPayload->getMessage();

                    $this->eventEmitter->emit(self::EVENT_PAYLOAD_RECEIVED, [$message]);
                } catch (\DomainException $e) {
                    $this->logger->error($e->getMessage());
                }

                $this->lastUpdateId = $update->updateId;
            }
        } catch (TimeoutException $timeoutException) {
            $this->logger->debug('/getUpdates timeout');
        } // @todo catch NotOk exception

        return  true;
    }
}