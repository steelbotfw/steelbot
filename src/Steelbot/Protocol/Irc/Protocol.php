<?php

namespace Steelbot\Protocol\Irc;

use Icicle\Socket\SocketInterface;
use Steelbot\ClientInterface;
use Steelbot\Protocol\Event\IncomingPayloadEvent;
use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Protocol\Irc\Entity\User;
use Steelbot\Protocol\OutgoingPayloadInterface;
use Steelbot\Protocol\Payload\Outgoing\TextMessage;
use Steelbot\Protocol\TextMessageInterface;

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
     * @var string
     */
    private $server;

    /**
     * @var int
     */
    private $port = 6667;

    /**
     * @var SocketInterface
     */
    private $socket;

    /**
     * Jannels bot joined to.
     * @var array
     */
    private $channels = [];

    /**
     * @return string
     */
    public function getServer(): string
    {
        return $this->server;
    }

    /**
     * @param string $server
     */
    public function setServer(string $server)
    {
        $this->server = $server;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return boolean
     */
    public function connect(): \Generator
    {
        $this->logger->info("Connecting to server");

        $this->socket = yield \Icicle\Socket\connect($this->server, $this->port, [
            'timeout' => 1000
        ]);
        yield from $this->socket->write("NICK steelbot\n");
        yield from $this->socket->write("USER steelbot steelbot steelbot :Steelbot Steelbot\n");

        $this->isConnected = true;
        $this->logger->info("Connected to server");
        $this->eventDispatcher->dispatch(static::EVENT_AFTER_CONNECT);

        return true;
    }

    /**
     * @return boolean
     */
    public function disconnect()
    {
        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_DISCONNECT);
        yield from $this->socket->end("QUIT\n");
        $this->isConnected = false;
        $this->eventDispatcher->dispatch(self::EVENT_AFTER_DISCONNECT);

        return true;
    }

    /**
     * Join to the given channel.
     *
     * @param string $channel
     *
     * @return bool|\Generator
     */
    public function join(string $channel)
    {
        if (isset($this->channels[$channel])) {
            return true;
        }

        yield from $this->command("JOIN $channel");
        $this->channels[$channel] = true;

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
            $line = sprintf("PRIVMSG %s :%s", $client->getId(), $payload->getText());
            return yield from $this->command($line);
        }

        throw new \DomainException("Unknown payload type");
    }

    /**
     * Process updates from server
     *
     * @return \Generator
     */
    public function processUpdates(): \Generator
    {
        $updates = yield $this->socket->read();
        foreach (explode("\r\n", $updates) as $updateStr) {
            if (empty($updateStr)) {
                continue;
            }

            $payload = $this->parseUpdate($updateStr);
            $event = new IncomingPayloadEvent($payload);
            $this->eventDispatcher->dispatch($event::NAME, $event);
        }

        return yield true;
    }

    /**
     * @param string $command
     *
     * @return \Generator
     */
    protected function command(string $command): \Generator
    {
        $command = trim($command) . "\n";

        yield from $this->socket->write($command);
    }

    protected function parseUpdate(string $update): IncomingPayloadInterface
    {
        list($sender, $command, $args) = explode(' ', $update, 3);
        switch ($command) {
            case 'PRIVMSG':
                list($fromStr, $message) = explode(':', $args, 2);
                $user = new User($sender);

                if ($this->isChannel($fromStr)) {
                    $from = new Client($fromStr);
                } else {
                    $from = new User($sender);
                }

                $payload = new \Steelbot\Protocol\Irc\Payload\Incoming\TextMessage($message, $from, $user);
                $this->logger->info("$update");

                return $payload;

            default:
                $this->logger->info("$update");

                return new IncomingPayload($update);
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    protected function isChannel(string $id): bool
    {
        return mb_substr($id, 0, 1) === '#';
    }
}
