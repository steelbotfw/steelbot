<?php

namespace Steelbot\Protocol\Telegram\Entity;

use Steelbot\ClientInterface;

class GroupChat implements ClientInterface
{
    public $id;
    public $title;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->title = $data['title'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->id;
    }
}