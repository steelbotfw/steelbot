<?php

namespace Steelbot\Tests\Context;

use Steelbot\{
    Context\AbstractContext,
    Protocol\AbstractProtocol,
    ClientInterface
};
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AbstractContextTest extends \PHPUnit_Framework_TestCase
{
    public function testIsResolved()
    {
        /** @var AbstractContext $context */
        $context = $this->getMockForAbstractClass(AbstractContext::class);

        $this->assertFalse($context->isResolved());
    }

    public function testGetSetClient()
    {
        /** @var AbstractContext $context */
        $context = $this->getMockForAbstractClass(AbstractContext::class);

        /** @var ClientInterface $client */
        $client = $this->createMock(ClientInterface::class);

        $context->setClient($client);

        $this->assertSame($client, $context->getClient());
    }

    public function testGetSetProtocol()
    {
        /** @var AbstractContext $context */
        $context = $this->getMockForAbstractClass(AbstractContext::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        /** @var AbstractProtocol $protocol */
        $protocol = $this->getMockForAbstractClass(AbstractProtocol::class, [$eventDispatcher]);

        $context->setProtocol($protocol);

        $this->assertSame($protocol, $context->getProtocol());
    }

    public function testResolve()
    {
        /** @var AbstractContext $context */
        $context = $this->getMockForAbstractClass(AbstractContext::class);

        $this->assertFalse($context->isResolved());

        $resolveCaller = function () {
            return $this->resolve();
        };

        $bound = $resolveCaller->bindTo($context, $context);
        $bound();

        $this->assertTrue($context->isResolved());
    }
}
