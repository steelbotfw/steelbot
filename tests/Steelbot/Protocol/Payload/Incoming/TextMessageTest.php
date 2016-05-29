<?php

namespace Steelbot\Tests\Protocol\Payload\Incoming;

use Steelbot\{
    ClientInterface, Protocol\Payload\Incoming\TextMessage, UserInterface
};

class TextMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetText()
    {
        /** @var ClientInterface $user */
        $user = $this->createMock(UserInterface::class);
        $payload = new TextMessage('incoming text', $user, $user);

        $this->assertEquals('incoming text', $payload->getText());
    }

    public function testToString()
    {
        /** @var ClientInterface $user */
        $user = $this->createMock(UserInterface::class);
        $payload = new TextMessage('incoming text', $user, $user);

        $this->assertEquals('incoming text', (string)$payload);
    }
}
