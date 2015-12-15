<?php

namespace Steelbot\Tests\Context;

use Steelbot\Application;
use Steelbot\ClientInterface;
use Steelbot\Context\Context;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    protected $context;

    public function setUp()
    {
        $app = new Application(Application::ENV_TEST, true);
        $client = $this->getMock('Steelbot\ClientInterface');
        $this->context = new Context($app, $client);
    }

    public function testIsResolved()
    {
        $this->assertInstanceOf(Context::class, $this->context);
    }
}
