<?php

namespace Steelbot\Protocol\Shell;

use Icicle\Socket\Stream;
use Steelbot\ClientInterface;
use Steelbot\Protocol\Event\IncomingPayloadEvent;
use Steelbot\Protocol\AbstractProtocol;
use Steelbot\Protocol\Payload\Incoming\TextMessage;

/**
 * Class Protocol
 * @todo
 */
class Protocol extends AbstractProtocol
{
    /**
     * @var Stream
     */
    protected $stdin;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @return boolean
     */
    public function connect()
    {
        $this->client = new Client();

        if (!$this->stdin instanceof Stream\ReadableStream) {
            $this->stdin = new Stream\ReadableStream(STDIN);
        }

        $this->eventDispatcher->dispatch(static::EVENT_AFTER_CONNECT);

        while ($this->isConnected()) {
            $this->prompt();
            $data = yield $this->stdin->read(0, "\n");
            $this->onData($data);
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function disconnect()
    {
        $this->eventDispatcher->dispatch(self::EVENT_BEFORE_DISCONNECT);
        unset($this->client);
        $this->eventDispatcher->dispatch(self::EVENT_AFTER_DISCONNECT);

        return true;
    }

    /**
     * @return boolean
     */
    public function isConnected()
    {
        return $this->client != null;
    }

    /**
     * @param \Steelbot\ClientInterface $client
     * @param $text
     *
     * @return mixed
     */
    public function send(ClientInterface $client, $text)
    {
        echo $text."\n";
    }

    /**
     * @return callable
     */
    protected function onData($data)
    {
        $data = trim($data);

        if (!$data) {
            return;
        }

        switch ($data) {
            case '/exit':
                $this->disconnect();
                return;
            case '/reconnect':
                $this->disconnect();
                $this->connect();
                return;
        }

        $message = new TextMessage($data, $this->client, $this->client);
        $this->eventDispatcher->dispatch(IncomingPayloadEvent::NAME, new IncomingPayloadEvent($message));
    }

    /**
     *
     */
    protected function prompt()
    {
        echo "> ";
    }
}
