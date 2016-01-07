<?php

namespace Steelbot\Tests\Context;

use Steelbot\Context\Context;
use Steelbot\Protocol\AbstractProtocol;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context
     */
    protected $context;

    public function setUp()
    {
        $client = $this->getMock('Steelbot\ClientInterface');
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $protocol = $this->getMockForAbstractClass(AbstractProtocol::class, [$eventDispatcher]);

        $this->context = $this->getMockForAbstractClass(Context::class, [$protocol, $client]);
    }

    public function testIsResolved()
    {
        $this->assertFalse($this->context->isResolved());
    }
}
