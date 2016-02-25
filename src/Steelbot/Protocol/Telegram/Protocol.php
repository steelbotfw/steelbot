<?php

namespace Steelbot\Protocol\Telegram;

use Icicle\Coroutine;
use Steelbot\ClientInterface;
use Steelbot\Protocol\{
    Event\IncomingPayloadEvent,
    IncomingPayloadInterface,
    OutgoingPayloadInterface,
    Payload\Outgoing\TextMessage,
    Telegram\Entity\Update,
    Telegram\Payload\Incoming\InlineQuery,
    Telegram\Payload\Outgoing\TextMessage as TelegramTextMessage,
    Payload\Incoming as IncomingPayload,
    Event\AfterConnectEvent,
    Event\BeforeDisconnectEvent,
    Exception\UnknownPayloadException
};

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
        $this->eventDispatcher->dispatch(AfterConnectEvent::NAME, new AfterConnectEvent($this));

        return true;
    }

    /**
     * @return boolean
     */
    public function disconnect()
    {
        $this->eventDispatcher->dispatch(BeforeDisconnectEvent::NAME, new BeforeDisconnectEvent($this));
        $this->isConnected = false;
        unset($this->api);
        $this->eventDispatcher->dispatch(self::EVENT_AFTER_DISCONNECT);

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
        // convert standard text message to telegram text message
        if ($payload instanceof TextMessage) {
            $payload = new TelegramTextMessage($payload->getText());
        }

        if ($payload instanceof TelegramTextMessage) {
            /** @var TelegramTextMessage $payload */
            return $this->api->sendMessage(
                $client->getId(),
                $payload->getText(),
                $payload->getParseMode(),
                $payload->getDisableWebPagePreview(),
                $payload->getDisableNotification(),
                $payload->getReplyToMessageId(),
                $payload->getReplyMarkup()
            );
        } /* elseif ($payload instanceof ImageMessage) {
            return $this->api->sendPhoto($client->getId(), $payload->getResource(), null, null, null);
        } */

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
    public function processUpdates(): \Generator
    {
        try {
            $updates = yield from $this->api->getUpdates($this->lastUpdateId);

            foreach ($updates as $update) {
                $payload = $this->createPayload($update);

                try {
                    $this->eventDispatcher->dispatch(IncomingPayloadEvent::NAME, new IncomingPayloadEvent($payload));
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

    /**
     * @param Update $update
     *
     * @return IncomingPayloadInterface
     */
    protected function createPayload(Update $update): IncomingPayloadInterface
    {
        if ($update->message !== null) {
            $m = $update->message;
            if (!empty($m->text)) {
                return new IncomingPayload\TextMessage($m->text, $m->chat, $m->from);
            } elseif (!empty($m->location)) {
                return new IncomingPayload\LocationMessage($m->location->longitude, $m->location->latitude, $m->chat, $m->from);
            } elseif (!empty($m->newChatParticipant)) {
                return new IncomingPayload\GroupChatNewParticipant($m->chat, $m->newChatParticipant);
            } else {
                throw new UnknownPayloadException($update, "Unknown message payload received");
            }
        } elseif ($update->inlineQuery !== null) {
            $iQ = $update->inlineQuery;
            return new InlineQuery($iQ->from, $iQ->id, $iQ->query, $iQ->from);
        }

        throw new UnknownPayloadException($update, "Empty message body");
    }
}
