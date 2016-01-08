<?php

namespace Steelbot\Tests\Protocol\Payload\Incoming;

use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Protocol\Payload\Incoming\TextMessage;

class TextMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetText()
    {
        $client = $this->getMock('Steelbot\ClientInterface');
        $payload = new TextMessage('incoming text', $client, $client);

        $this->assertEquals('incoming text', $payload->getText());
    }

    public function testGetType()
    {
        $client = $this->getMock('Steelbot\ClientInterface');
        $payload = new TextMessage('incoming text', $client, $client);

        $this->assertEquals(IncomingPayloadInterface::TYPE_TEXT, $payload->getType());
    }

    public function testToString()
    {
        $client = $this->getMock('Steelbot\ClientInterface');
        $payload = new TextMessage('incoming text', $client, $client);

        $this->assertEquals('incoming text', (string)$payload);
    }
}
