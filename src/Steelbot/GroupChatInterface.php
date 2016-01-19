<?php

namespace Steelbot;

/**
 * Interface GroupChatInterface
 */
interface GroupChatInterface extends ClientInterface
{
    /**
     * Get participants list if available
     *
     * @return ClientInterface[]|false
     */
    public function getParticipants();
}
