<?php

namespace Steelbot\Protocol\Telegram\Entity;

/**
 * Class ReplyKeyboardHide
 * @package Telegram\Entity
 */
class ReplyKeyboardHide implements \JsonSerializable
{
    /**
     *  @var bool
     */
    public $hideKeyboard = true;

    /**
     * @var boolean
     */
    public $selective = null;

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $data = new \stdClass();
        $data->hide_keyboard = $this->hideKeyboard;
        if ($this->selective !== null) {
            $data->selective = $this->selective;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this, JSON_UNESCAPED_UNICODE);
    }
}