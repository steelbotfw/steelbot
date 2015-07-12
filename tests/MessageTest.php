<?php

namespace Steelbot\Tests;

use Steelbot\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $client = $this->getMock('Steelbot\ClientInterface');
        $dateTime = new \DateTimeImmutable();

        $message = new Message($client, 'Test text', $dateTime);
        $this->assertEquals('Test text', (string)$message);
    }
}