<?php

namespace Steelbot\Context;

use Steelbot\Protocol\IncomingPayloadInterface;

interface ContextProviderInterface
{
    /**
     * Find context by given payload.
     *
     * @param IncomingPayloadInterface $payload
     *
     * @return mixed
     */
    public function findContext(IncomingPayloadInterface $payload);
}
