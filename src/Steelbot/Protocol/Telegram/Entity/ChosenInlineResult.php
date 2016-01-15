<?php

namespace Steelbot\Protocol\Telegram\Entity;

class ChosenInlineResult
{
    /**
     * @var string
     */
    public $resultId;

    /**
     * @var User
     */
    public $from;

    /**
     * @var string
     */
    public $query;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->resultId = $data['result_id'];
        $this->from  = new User($data['from']);
        $this->query = $data['query'];
    }
}
