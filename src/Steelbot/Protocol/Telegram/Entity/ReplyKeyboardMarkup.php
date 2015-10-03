<?php

namespace Steelbot\Protocol\Telegram\Entity;

/**
 * Class Message
 * @package Telegram\Entity
 */
class ReplyKeyboardMarkup implements \JsonSerializable
{
    /**
     * @var string[][]
     */
    public $keyboard;

    /**
     * @var boolean
     */
    public $resizeKeyboard = null;

    /**
     * @var boolean
     */
    public $oneTimeKeyboard = null;

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
        $data->keyboard = $this->keyboard;

        if ($this->resizeKeyboard !== null) {
            $data->resize_keyboard = $this->resizeKeyboard;
        }
        if ($this->oneTimeKeyboard !== null) {
            $data->one_time_keyboard = $this->oneTimeKeyboard;
        }
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