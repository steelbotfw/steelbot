<?php

namespace Steelbot\Protocol\Telegram\Entity;
use Steelbot\ClientInterface;

/**
 * Class User
 * @package Telegram\Entity
 */
class User implements ClientInterface
{
    public $id;
    public $firstName;
    public $lastName;
    public $username;

    /**
     * @param array $data
     */
    public function __construct(array $data) {
        $this->id = $data['id'];
        $this->firstName = $data['first_name'] ?? null;
        $this->lastName  = $data['last_name']  ?? null;
        $this->username  = $data['username']   ?? null;
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
