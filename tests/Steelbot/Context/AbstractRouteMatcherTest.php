<?php

namespace Steelbot\Tests\Context;

use Steelbot\Application;
use Steelbot\Context\AbstractRouteMatcher;

class  AbstractRouteMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractRouteMatcher
     */
    protected $abstractRouteMatcher;

    public function setUp()
    {
        $this->abstractRouteMatcher = $this->getMockForAbstractClass(AbstractRouteMatcher::class);
    }

    public function testSetPrivateChat()
    {
        $this->assertTrue($this->abstractRouteMatcher->getPrivateChat());
        $this->abstractRouteMatcher->setPrivateChat(false);
        $this->assertFalse($this->abstractRouteMatcher->getPrivateChat());
    }

    public function testSetGroupChat()
    {
        $this->assertFalse($this->abstractRouteMatcher->getGroupChat());
        $this->abstractRouteMatcher->setGroupChat(true);
        $this->assertTrue($this->abstractRouteMatcher->getGroupChat());
    }

    public function testGetPriority()
    {
        $this->assertEquals(0, $this->abstractRouteMatcher->getPriority());
    }

    public function testHelp()
    {
        $this->assertCount(0, $this->abstractRouteMatcher->getHelp());
        $this->abstractRouteMatcher->setHelp([
            '/help' => 'SHow help'
        ]);
        $this->assertArrayHasKey('/help', $this->abstractRouteMatcher->getHelp());
        $this->assertCount(1, $this->abstractRouteMatcher->getHelp());
    }
}