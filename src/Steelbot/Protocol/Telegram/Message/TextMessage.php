<?php

namespace Steelbot\Protocol\Telegram\Message;

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
    public function __construct(string $text, ClientInterface $from, ClientInterface $user)
    {
        parent::__construct($from, $user);
        $this->text = $text;
    }

    /**
     * @return \Steelbot\Protocol\Telegram\Message\string|string
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