<?php

namespace Steelbot\Tests\Protocol\Payload\Outgoing;

use Steelbot\Protocol\Payload\Outgoing\ImageMessage;

class ImageMessageTest extends \PHPUnit_Framework_TestCase
{
    const FILE = '/tmp/filename.jpg';

    public function setUp()
    {
        touch(self::FILE);
    }

    public function tearDown()
    {
        unlink(self::FILE);
    }

    public function testGetFilename()
    {
        $payload = new ImageMessage(self::FILE);

        $this->assertEquals(self::FILE, $payload->getFilename());
    }

    public function testGetResource()
    {
        $payload = new ImageMessage(self::FILE);

        $this->assertTrue(is_resource($payload->getResource()));
    }
}
