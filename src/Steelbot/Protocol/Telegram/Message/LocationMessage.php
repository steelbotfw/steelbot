<?php

namespace Steelbot\Protocol\Telegram\Message;

use Steelbot\ClientInterface;
use Steelbot\Protocol\LocationMessageInterface;

/**
 * Class LocationMessage
 *
 * @package Steelbot\Protocol\Telegram\Message
 */
class LocationMessage extends AbstractMessage implements LocationMessageInterface
{
    /**
     * @var float
     */
    private $longitude;

    /**
     * @var float
     */
    private $latitude;

    /**
     * @param string $text
     * {@inheritdoc}
     */
    public function __construct(float $longitude, float $latitude, ClientInterface $from, ClientInterface $user)
    {
        parent::__construct($from, $user);

        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude() : float
    {
        return $this->longitude;
    }

    /**
     * @return float
     */
    public function getLatitude() : float
    {
        return $this->latitude;
    }

    public function __toString() : string
    {
        return "lon:{$this->longitude}, lat:{$this->latitude}";
    }
}