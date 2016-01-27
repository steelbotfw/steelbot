<?php

namespace Steelbot\Protocol\Payload\Outgoing;

use Steelbot\Protocol\OutgoingPayloadInterface;

/**
 * Class Text represents outgoing text message
 *
 * @package Steelbot\Protocol\Payload\Outgoing
 */
class TextMessage implements OutgoingPayloadInterface
{
    /**
     * @var string
     */
    protected $text;

    /**
     * Text constructor.
     *
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }
}
