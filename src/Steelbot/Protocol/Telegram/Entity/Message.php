<?php

namespace Steelbot\Protocol\Telegram\Entity;

/**
 * Class Message
 * @package Telegram\Entity
 */
class Message
{
    /**
     * @var integer
     */
    public $messageId;

    /**
     * @var User
     */
    public $from;

    /**
     * @var integer
     */
    public $date;

    /**
     * @var User|GroupChat
     */
    public $chat;

    /**
     * @var string
     */
    public $text;

    /**
     * @var null|\Steelbot\Protocol\Telegram\Entity\Location
     */
    public $location;

    // @todo forwardFrom

    // @todo forwardDate

    // @todo replyToMessage

    // @todo ...

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->messageId = $data['message_id'];
        $this->from      = new User($data['from']);
        $this->chat      = isset($data['title']) ? new GroupChat($data['chat']) : new User($data['chat']);
        $this->date      = \DateTimeImmutable::createFromFormat('U', $data['date']);
        $this->text      = isset($data['text']) ? $data['text'] : null;
        $this->location  = isset($data['location']) ? new Location($data['location']) : null;
    }
}