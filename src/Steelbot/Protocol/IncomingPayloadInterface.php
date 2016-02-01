<?php

namespace Steelbot\Protocol;

/**
 * Interface IncomingPayloadInterface
 * @package Steelbot\Protocol
 */
interface IncomingPayloadInterface
{

    /**
     * String representation of the payload.
     *
     * @return string
     */
    public function __toString(): string;
}
