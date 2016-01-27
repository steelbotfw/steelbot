<?php

namespace Steelbot\Protocol\Payload\Incoming;

use Steelbot\ClientInterface;
use Steelbot\Protocol\LocationMessageInterface;

/**
 * Class LocationMessage
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
     * @param float           $longitude
     * @param float           $latitude
     * @param ClientInterface $from
     * @param ClientInterface $user
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

    /**
     * Payload type.
     *
     * @return string
     */
    public function getType(): string
    {
        return static::TYPE_LOCATION;
    }
}
