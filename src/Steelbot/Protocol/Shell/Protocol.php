<?php

namespace Steelbot\Protocol\Shell;

use React\Stream\Stream;
use Steelbot\ClientInterface;
use Steelbot\Message;
use Steelbot\Protocol\AbstractProtocol;

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

    protected $client;

    /**
     * @return boolean
     */
    public function connect()
    {
        $this->client = new Client();
        if ($this->stdin instanceof Stream) {
            $this->stdin->resume();
        } else {
            $this->stdin = new Stream(STDIN, $this->loop);
        }
        $this->stdin->on('data', $this->onData());

        $this->prompt();
        $this->eventEmitter->emit('protocol.postConnect');
        return true;
    }

    /**
     * @return boolean
     */
    public function disconnect()
    {
        $this->eventEmitter->emit('protocol.preDisconnect');
        unset($this->client);
        $this->stdin->pause();
        $this->eventEmitter->emit('protocol.postDisconnect');

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
    protected function onData()
    {
        return function ($data) {
            $data = trim($data);

            switch ($data) {
                case '/exit':
                    $this->disconnect();
                    return;
                case '/reconnect':
                    $this->disconnect();
                    $this->connect();
                    return;
            }

            $message = new Message($this->client, $data, new \DateTimeImmutable());
            $this->eventEmitter->emit('message', [$message]);
            $this->prompt();
        };
    }

    /**
     *
     */
    protected function prompt()
    {
        echo "> ";
    }
}