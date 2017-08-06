<?php

namespace Steelbot\Protocol\Irc\Payload\Incoming;

class TextMessage extends \Steelbot\Protocol\Payload\Incoming\TextMessage
{
    /**
     * @return bool
     */
    public function isGroupChatMessage()
    {
        return mb_substr($this->from->getId(), 0, 1) === '#';
    }
}