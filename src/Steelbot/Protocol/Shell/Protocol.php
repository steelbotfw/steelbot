<?php

namespace Steelbot\Protocol\Shell;

use Icicle\Socket\Stream;
use Steelbot\ClientInterface;
use Steelbot\Message;
use Steelbot\Protocol\AbstractProtocol;
use Steelbot\Protocol\Shell\Message\TextMessage;

/**
 * Class Protocol
 * @package Steelbot\Protocol\Shell
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

        $this->eventEmitter->emit(self::EVENT_POST_CONNECT);

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
        $this->eventEmitter->emit(self::EVENT_PRE_DISCONNECT);
        unset($this->client);
        $this->eventEmitter->emit(self::EVENT_POST_DISCONNECT);

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

        $message = new TextMessage($data, $this->client);
        $this->eventEmitter->emit(self::EVENT_PAYLOAD_RECEIVED, [$message]);
    }

    /**
     *
     */
    protected function prompt()
    {
        echo "> ";
    }
}