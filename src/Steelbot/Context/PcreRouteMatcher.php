<?php

namespace Steelbot\Context;

use Steelbot\Protocol\TextMessageInterface;

class PcreRouteMatcher implements RouteMatcherInterface
{
    /**
     * @var string
     */
    protected $regexp;

    /**
     * @var bool|true
     */
    protected $enablePrivateChat;

    /**
     * @var bool|true
     */
    protected $enableGroupChat;

    /**
     * @param \Steelbot\Context\string $regexp
     * @param bool|true $enablePrivateChat
     * @param bool|true $enableGroupChat
     */
    public function __construct(string $regexp, $enablePrivateChat = true, $enableGroupChat = true)
    {
        $this->regexp = $regexp;
        $this->enablePrivateChat = $enablePrivateChat;
        $this->enableGroupChat = $enableGroupChat;
    }

    public function match($payload) : bool
    {
        return ($payload instanceof TextMessageInterface) &&
        (
            ($this->enablePrivateChat && $payload->getFrom()->getId() === $payload->getUser()->getId()) ||
            ($this->enableGroupChat && $payload->getFrom()->getId() !== $payload->getUser()->getId())
        ) &&
        (preg_match($this->regexp, (string)$payload));
    }
}