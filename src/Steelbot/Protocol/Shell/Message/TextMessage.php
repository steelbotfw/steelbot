<?php

namespace Steelbot\Protocol\Shell\Message;

use Steelbot\ClientInterface;
use Steelbot\Protocol\TextMessageInterface;

class TextMessage extends AbstractMessage implements TextMessageInterface
{
    /**
     * @var string
     */
    private $text;

    /**
     * @param string $text
     * {@inheritdoc}
     */
    public function __construct(string $text, ClientInterface $from)
    {
        parent::__construct($from, $from);
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText() : string
    {
        return $this->text;
    }

    public function __toString() : string
    {
        return $this->text;
    }
}