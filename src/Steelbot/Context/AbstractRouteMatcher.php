<?php

namespace Steelbot\Context;

use Steelbot\Protocol\IncomingPayloadInterface;

/**
 * Class AbstractRouteMatcher
 * @package Steelbot\Context
 */
abstract class AbstractRouteMatcher implements RouteMatcherInterface
{
    /**
     * @var bool|true
     */
    protected $enablePrivateChat = true;

    /**
     * @var bool|true
     */
    protected $enableGroupChat = false;

    /**
     * @var int
     */
    protected $priority = 0;

    protected $help = [];

    /**
     * @param $payload
     *
     * @return bool
     */
    abstract public function match(IncomingPayloadInterface $payload): bool;

    /**
     * @param bool $enabled
     */
    public function setPrivateChat(bool $enabled)
    {
        $this->enablePrivateChat = $enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setGroupChat(bool $enabled)
    {
        $this->enableGroupChat = $enabled;
    }

    /**
     * @return bool
     */
    public function getPrivateChat(): bool
    {
        return $this->enablePrivateChat;
    }

    /**
     * @return bool
     */
    public function getGroupChat(): bool
    {
        return $this->enableGroupChat;
    }

    /**
     * Matcher priority
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param array $help
     */
    public function setHelp(array $help = [])
    {
        $this->help = $help;
    }

    public function getHelp(): array
    {
        return $this->help;
    }

}