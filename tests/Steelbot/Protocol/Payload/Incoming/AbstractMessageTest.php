<?php

namespace Steelbot\Tests\Protocol\Payload\Incoming;

use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Protocol\Payload\Incoming\AbstractMessage;

class AbstractMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFrom()
    {
        $from = $this->getMock(\Steelbot\ClientInterface::class);
        $user = $this->getMock(\Steelbot\UserInterface::class);

        /** @var AbstractMessage $payload */
        $payload = $this->getMockForAbstractClass(AbstractMessage::class, [$from, $user]);

        $this->assertSame($from, $payload->getFrom());
        $this->assertNotSame($user, $payload->getFrom());
    }

    public function testGetUser()
    {
        $from = $this->getMock(\Steelbot\ClientInterface::class);
        $user = $this->getMock(\Steelbot\UserInterface::class);

        /** @var AbstractMessage $payload */
        $payload = $this->getMockForAbstractClass(AbstractMessage::class, [$from, $user]);

        $this->assertSame($user, $payload->getUser());
        $this->assertNotSame($from, $payload->getUser());
    }

    public function testIsGroupChatMessage()
    {
        $from = $this->getMock(\Steelbot\GroupChatInterface::class);
        $from->method('getId')->willReturn(-123);

        $user = $this->getMock(\Steelbot\UserInterface::class);
        $user->method('getId')->willReturn(42);

        /** @var AbstractMessage $payload */
        $payload = $this->getMockForAbstractClass(AbstractMessage::class, [$user, $user]);
        /** @var AbstractMessage $groupPayload */
        $groupPayload = $this->getMockForAbstractClass(AbstractMessage::class, [$from, $user]);

        $this->assertFalse($payload->isGroupChatMessage());
        $this->assertTrue($groupPayload->isGroupChatMessage());
    }
}
