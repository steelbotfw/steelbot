<?php

namespace Steelbot\Protocol\Telegram\Entity;

class Location
{
    public $longitude;
    public $latitude;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->longitude = $data['longitude'];
        $this->latitude = $data['latitude'];
    }
}