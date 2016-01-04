<?php

namespace Steelbot\Tests\Context;

use Steelbot\Context\ContextProvider;
use Steelbot\Route\PcreRouteMatcher;

/**
 * Class ContextProviderTest
 * @package Steelbot\Tests\Context
 */
class ContextProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testSetRouteStringMatcher()
    {
        $contextProvider = new ContextProvider();
        $this->assertCount(0, $contextProvider->getRoutes());

        $matcher = '~a~';
        $handler = function () {
            return true;
        };
        $contextProvider->setRoute($matcher, $handler);

        $this->assertCount(1, $contextProvider->getRoutes());
    }

    public function testSetRouteCallableMatcher()
    {
        $contextProvider = new ContextProvider();
        $this->assertCount(0, $contextProvider->getRoutes());

        $matcher = new PcreRouteMatcher('~a~');
        $handler = function () {
            return true;
        };
        $help = [
            'a' => 'Help text'
        ];

        $contextProvider->setRoute($matcher, $handler, $help);

        $this->assertCount(1, $contextProvider->getRoutes());
    }
}
