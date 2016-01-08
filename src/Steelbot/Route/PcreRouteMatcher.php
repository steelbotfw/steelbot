<?php

namespace Steelbot\Route;

use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Protocol\TextMessageInterface;

/**
 * Class PcreRouteMatcher
 *
 * @package Steelbot\Context
 */
class PcreRouteMatcher extends AbstractRouteMatcher
{
    /**
     * @var string
     */
    protected $regexp;

    /**
     * @param string $regexp
     * @param bool|true $enablePrivateChat
     * @param bool|true $enableGroupChat
     */
    public function __construct(string $regexp, int $priority = 0)
    {
        $this->regexp = $regexp;
        $this->priority = $priority;
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
            ($this->enablePrivateChat && !$payload->isGroupChatMessage()) ||
            ($this->enableGroupChat && $payload->isGroupChatMessage())
        ) &&
        (preg_match($this->regexp, (string)$payload));
    }
}
