<?php

namespace Steelbot\Protocol\Gitter;

use Steelbot\Protocol\IncomingPayloadInterface;

/**
 * @todo
 */
class IncomingPayload implements IncomingPayloadInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $data;

    /**
     * @param string $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function __toString(): string
    {
        return (string)$this->data;
    }
}
