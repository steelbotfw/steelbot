<?php

namespace Steelbot\Tests\Protocol\Payload\Outgoing;

use Steelbot\Protocol\Payload\Outgoing\ImageMessage;

class ImageMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFilename()
    {
        $payload = new ImageMessage('filename.jpg');

        $this->assertEquals('filename.jpg', $payload->getFilename());
    }
}
