<?php

namespace Steelbot\Context;

use Steelbot\Protocol\IncomingPayloadInterface;

interface ContextInterface
{
    public function handle(IncomingPayloadInterface $payload);

    /**
     * @return boolean
     */
    public function isResolved() : bool;
}
