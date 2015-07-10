<?php

namespace Steelbot;

/**
 * Class Message
 * @package Steelbot
 */
class Message 
{
    /**
     * @var \Steelbot\ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var \DateTimeInterface
     */
    protected $timestamp;

    /**
     * @param string $text
     * @param \DateTimeInterface $timestamp
     */
    public function __construct(ClientInterface $client, $text, \DateTimeInterface $timestamp)
    {
        $this->client = $client;
        $this->text = $text;
        $this->timestamp = $timestamp;
    }

    /**
     * @return \Steelbot\ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->text;
    }
} 