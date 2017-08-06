<?php

namespace Steelbot\Protocol\Irc\Entity;
use Steelbot\UserInterface;

/**
 * Class User
 * @package Telegram\Entity
 */
class User implements UserInterface
{
    public $id;

    /**
     * @param array $data
     */
    public function __construct(string $id) {
        if (mb_substr($id, 0 ,1) === ':') {
            $id = mb_substr($id, 1);
        }
        list($nick, $host) = explode('!', $id);

        $this->id = $nick;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->id;
    }
}