<?php

namespace Steelbot\Protocol\Exception;

class UnknownPayloadException extends \DomainException
{
    protected $payload;

    public function __construct($payload, $message = '', $code = 0, \Exception $previous = null)
    {
        $this->payload = $payload;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
