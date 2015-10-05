<?php

namespace Steelbot\Context;

use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Protocol\TextMessageInterface;

/**
 * Class PcreRouteMatcher
 *
 * @package Steelbot\Context
 */
class PcreRouteMatcher implements RouteMatcherInterface
{
    /**
     * @var string
     */
    protected $regexp;

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
     * @param \Steelbot\Context\string $regexp
     * @param bool|true $enablePrivateChat
     * @param bool|true $enableGroupChat
     */
    public function __construct(string $regexp, int $priority = 0)
    {
        $this->regexp = $regexp;
        $this->priority = $priority;
    }

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

    /**
     * @param \Steelbot\Protocol\IncomingPayloadInterface $payload
     *
     * @return bool
     */
    public function match(IncomingPayloadInterface $payload): bool
    {
        return ($payload instanceof TextMessageInterface) &&
        (
            ($this->enablePrivateChat && $payload->getFrom()->getId() === $payload->getUser()->getId()) ||
            ($this->enableGroupChat && $payload->getFrom()->getId() !== $payload->getUser()->getId())
        ) &&
        (preg_match($this->regexp, (string)$payload));
    }
}