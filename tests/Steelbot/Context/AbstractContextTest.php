<?php

namespace Steelbot\Tests\Context;

use Steelbot\Context\AbstractContext;
use Steelbot\Protocol\AbstractProtocol;
use Steelbot\ClientInterface;

class AbstractContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractContext
     */
    protected $context;

    public function setUp()
    {
        $client = $this->getMock(ClientInterface::class);
        $eventDispatcher = $this->getMock(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class);
        $protocol = $this->getMockForAbstractClass(AbstractProtocol::class, [$eventDispatcher]);

        $context = $this->getMock(AbstractContext::class);
        $context->setClient($client);
        $context->setProtocol($protocol);

        $this->context = $context;
    }

    public function testIsResolved()
    {
        $this->assertFalse($this->context->isResolved());
    }
}
