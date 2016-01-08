<?php

namespace Steelbot\Protocol\Payload\Outgoing;

use Steelbot\Protocol\OutgoingPayloadInterface;

class ImageMessage implements OutgoingPayloadInterface
{
    /**
     * @var string
     */
    private $filename;

    /**
     * Image constructor.
     *
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        $resource = fopen($this->filename, 'r');

        return $resource;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}
