<?php

namespace Steelbot\Tests\Protocol\Payload\Incoming;

use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Protocol\Payload\Incoming\TextMessage;

class TextMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetText()
    {
        $user = $this->getMock(\Steelbot\UserInterface::class);
        $payload = new TextMessage('incoming text', $user, $user);

        $this->assertEquals('incoming text', $payload->getText());
    }

    public function testGetType()
    {
        $user = $this->getMock(\Steelbot\UserInterface::class);
        $payload = new TextMessage('incoming text', $user, $user);

        $this->assertEquals(IncomingPayloadInterface::TYPE_TEXT, $payload->getType());
    }

    public function testToString()
    {
        $user = $this->getMock(\Steelbot\UserInterface::class);
        $payload = new TextMessage('incoming text', $user, $user);

        $this->assertEquals('incoming text', (string)$payload);
    }
}
