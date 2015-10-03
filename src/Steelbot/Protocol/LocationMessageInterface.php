<?php

namespace Steelbot\Protocol;

interface LocationMessageInterface
{
    /**
     * @return float
     */
    public function getLongitude() : float;

    /**
     * @return float
     */
    public function getLatitude() : float;
}