<?php

namespace Steelbot\Protocol;

/**
 * Interface IncomingPayloadInterface
 * @package Steelbot\Protocol
 */
interface IncomingPayloadInterface
{
    const TYPE_TEXT = 'text';
    const TYPE_LOCATION = 'location';
    const TYPE_IMAGE = 'image';

    /**
     * Payload type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * String representation of the payload.
     *
     * @return string
     */
    public function __toString(): string;
}
