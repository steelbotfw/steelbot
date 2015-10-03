<?php
namespace Steelbot\Protocol\Telegram\Entity;

class Update
{
    /**
     * @var integer
     */
    public $updateId;

    /**
     * @var Message
     */
    public $message;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->updateId = $data['update_id'];
        $this->message = new Message($data['message']);
    }
}