<?php

namespace Steelbot\Context;

use Steelbot\ClientInterface;
use Steelbot\Protocol\AbstractProtocol;

interface ContextInterface
{
    public function handle($payload);

    /**
     * @return boolean
     */
    public function isResolved() : bool;

    /**
     * Set client for context.
     *
     * @param ClientInterface $client
     *
     * @return mixed
     */
    public function setClient(ClientInterface $client);

    /**
     * Set protocol for context.
     *
     * @param AbstractProtocol $protocol
     *
     * @return mixed
     */
    public function setProtocol(AbstractProtocol $protocol);
}
