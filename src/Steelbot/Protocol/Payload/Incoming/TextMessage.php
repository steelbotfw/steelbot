<?php

namespace Steelbot\Protocol\Payload\Incoming;

use Steelbot\ClientInterface;
use Steelbot\Protocol\TextMessageInterface;

/**
 * Class TextMessage
 */
class TextMessage extends AbstractMessage implements TextMessageInterface
{
    /**
     * @var string
     */
    private $text;

    /**
     * @param string|string   $text
     * @param ClientInterface $from
     * @param ClientInterface $user
     */
    public function __construct(string $text, ClientInterface $from, ClientInterface $user)
    {
        parent::__construct($from, $user);
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    public function __toString(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_TEXT;
    }
}
