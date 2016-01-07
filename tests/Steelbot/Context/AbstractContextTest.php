<?php

namespace Steelbot\Tests\Context;

use Steelbot\Context\AbstractContext;
use Steelbot\Protocol\AbstractProtocol;

class AbstractContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractContext
     */
    protected $context;

    public function setUp()
    {
        $client = $this->getMock('Steelbot\ClientInterface');
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $protocol = $this->getMockForAbstractClass(AbstractProtocol::class, [$eventDispatcher]);

        $this->context = $this->getMockForAbstractClass(AbstractContext::class, [$protocol, $client]);
    }

    public function testIsResolved()
    {
        $this->assertFalse($this->context->isResolved());
    }
}
