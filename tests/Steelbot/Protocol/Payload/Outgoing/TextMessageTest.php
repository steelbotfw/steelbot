<?php

namespace Steelbot\Tests\Protocol\Payload\Outgoing;

use Steelbot\Protocol\Payload\Outgoing\TextMessage;

class TextMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetText()
    {
        $payload = new TextMessage('test text');

        $this->assertEquals('test text', $payload->getText());
    }
}
