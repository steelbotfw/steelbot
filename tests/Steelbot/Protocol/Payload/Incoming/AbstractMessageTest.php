<?php

namespace Steelbot\Tests\Protocol\Payload\Incoming;

use Steelbot\{
    GroupChatInterface, Protocol\Payload\Incoming\AbstractMessage, ClientInterface, UserInterface
};

class AbstractMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFrom()
    {
        $from = $this->createMock(ClientInterface::class);
        $user = $this->createMock(UserInterface::class);

        /** @var AbstractMessage $payload */
        $payload = $this->getMockForAbstractClass(AbstractMessage::class, [$from, $user]);

        $this->assertSame($from, $payload->getFrom());
        $this->assertNotSame($user, $payload->getFrom());
    }

    public function testGetUser()
    {
        $from = $this->createMock(ClientInterface::class);
        $user = $this->createMock(UserInterface::class);

        /** @var AbstractMessage $payload */
        $payload = $this->getMockForAbstractClass(AbstractMessage::class, [$from, $user]);

        $this->assertSame($user, $payload->getUser());
        $this->assertNotSame($from, $payload->getUser());
    }

    public function testIsGroupChatMessage()
    {
        $from = $this->createMock(GroupChatInterface::class);
        $from->method('getId')->willReturn(-123);

        $user = $this->createMock(UserInterface::class);
        $user->method('getId')->willReturn(42);

        /** @var AbstractMessage $payload */
        $payload = $this->getMockForAbstractClass(AbstractMessage::class, [$user, $user]);
        /** @var AbstractMessage $groupPayload */
        $groupPayload = $this->getMockForAbstractClass(AbstractMessage::class, [$from, $user]);

        $this->assertFalse($payload->isGroupChatMessage());
        $this->assertTrue($groupPayload->isGroupChatMessage());
    }
}
