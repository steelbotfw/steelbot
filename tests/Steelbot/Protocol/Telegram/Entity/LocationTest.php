<?php

namespace Steelbot\Tests\Protocol\Telegram\Entity;

use Steelbot\Protocol\Telegram\Entity\Location;

class LocationTest extends \PHPUnit_Framework_TestCase
{
    public function testEntity()
    {
        $data = ['longitude' => 1.23, 'latitude' => 4.56];
        $location = new Location($data);

        $this->assertEquals(1.23, $location->longitude);
        $this->assertEquals(4.56, $location->latitude);
    }
}
