<?php

namespace {
    $mockFileExists = true;
}

namespace Steelbot\Context {
    function file_exists($filename)
    {
        global $mockFileExists;
        if (isset($mockFileExists) && $mockFileExists === true) {
            return true;
        } else {
            return \file_exists($filename);
        }
    }
}

namespace Steelbot\Tests\Context {

    use Steelbot\Application;
    use Steelbot\Context\ContextProvider;
    use Steelbot\Context\AbstractContext;
    use Steelbot\Protocol\AbstractProtocol;
    use Steelbot\Protocol\Payload\Incoming\TextMessage;
    use Steelbot\Route\PcreRouteMatcher;
    use Symfony\Component\DependencyInjection\ContainerInterface;


    /**
     * Class ContextProviderTest
     * @package Steelbot\Tests\Context
     */
    class ContextProviderTest extends \PHPUnit_Framework_TestCase
    {
        public function testAddRouteStringMatcher()
        {
            $contextProvider = new ContextProvider($this->getMock(ContainerInterface::class));
            $this->assertCount(0, $contextProvider->getRoutes());

            $matcher = '~a~';
            $handler = function () {
                return true;
            };
            $contextProvider->addRoute($matcher, $handler);

            $this->assertCount(1, $contextProvider->getRoutes());
        }

        public function testAddRouteCallableMatcher()
        {
            $contextProvider = new ContextProvider($this->getMock(ContainerInterface::class));
            $this->assertCount(0, $contextProvider->getRoutes());

            $matcher = new PcreRouteMatcher('~a~');
            $handler = function () {
                return true;
            };
            $help = [
                'a' => 'Help text'
            ];

            $contextProvider->addRoute($matcher, $handler, $help);

            $this->assertCount(1, $contextProvider->getRoutes());
        }

        public function testGetRoutes()
        {
            $contextProvider = new ContextProvider($this->getMock(ContainerInterface::class));
            $this->assertCount(0, $contextProvider->getRoutes());

            $matcher = new PcreRouteMatcher('~a~');
            $handler = function () {
                return true;
            };

            $matcher2 = new PcreRouteMatcher('~b~');

            $contextProvider->addRoute($matcher, $handler);
            $contextProvider->addRoute($matcher2, $handler);

            $getRoutes = $contextProvider->getRoutes();
            $this->assertCount(2, $getRoutes);
            $this->assertSame([[$matcher, $handler], [$matcher2, $handler]], $getRoutes);
        }

        public function testFindContextNotFound()
        {
            $environment = 'test';
            $contextProvider = new ContextProvider($this->getMock(ContainerInterface::class));
            $logger = $this->getMock(\Psr\Log\LoggerInterface::class);
            $contextProvider->setLogger($logger);

            $payload = $this->getMock('Steelbot\Protocol\IncomingPayloadInterface');
            $client = $this->getMock('Steelbot\ClientInterface');
            $app = new Application($environment, true);

            $this->assertFalse($contextProvider->findContext($payload, $client, $app));
        }

        public function testFindContextCallableFound()
        {
            $contextProvider = new ContextProvider();
            $logger = $this->getMock(\Psr\Log\LoggerInterface::class);
            $contextProvider->setLogger($logger);

            /** @var \Steelbot\ClientInterface $user */
            $user = $this->getMock(\Steelbot\UserInterface::class);
            $payload = new TextMessage('a', $user, $user);

            $matcher = new PcreRouteMatcher('~a~');
            $handler = function () {
                return true;
            };
            $help = [
                'a' => 'Help text'
            ];
            $contextProvider->addRoute($matcher, $handler, $help);

            $foundHandler = $contextProvider->findContext($payload);

            $this->assertSame($handler, $foundHandler);
        }

        public function testFindContextClassFound()
        {
            $contextProvider = new ContextProvider();
            $logger = $this->getMock(\Psr\Log\LoggerInterface::class);
            $contextProvider->setLogger($logger);

            /** @var \Steelbot\UserInterface $user */
            $user = $this->getMock(\Steelbot\UserInterface::class);
            $payload = new TextMessage('a', $user, $user);

            $matcher = new PcreRouteMatcher('~a~');

            $handler = $this->getMockForAbstractClass(AbstractContext::class);
            $help = [
                'a' => 'Help text'
            ];
            $contextProvider->addRoute($matcher, get_class($handler), $help);

            $foundHandler = $contextProvider->findContext($payload);
            $this->assertInstanceOf(AbstractContext::class, $foundHandler);
        }

        public function testFindContextFileFound()
        {
            $handler = function () {
                return true;
            };

            $contextProvider = $this->getMockBuilder(ContextProvider::class)
                ->setMethods(['createContextFromFile'])
                ->getMock();
            $contextProvider->method('createContextFromFile')
                ->willReturn($handler);

            $logger = $this->getMock(\Psr\Log\LoggerInterface::class);
            $contextProvider->setLogger($logger);

            /** @var \Steelbot\UserInterface $user */
            $user = $this->getMock(\Steelbot\UserInterface::class);
            $payload = new TextMessage('a', $user, $user);

            $matcher = new PcreRouteMatcher('~a~');
            $handlerFilename = 'handler.php';
            $help = [
                'a' => 'Help text'
            ];
            $contextProvider->addRoute($matcher, $handlerFilename, $help);

            $foundHandler = $contextProvider->findContext($payload, $user);
            $this->assertSame($handler, $foundHandler);
        }

        public function testFindContextErrorResolving()
        {
            global $mockFileExists;
            $mockFileExists = false;

            $contextProvider = new ContextProvider($this->getMock(ContainerInterface::class));

            $logger = $this->getMock(\Psr\Log\LoggerInterface::class);
            $contextProvider->setLogger($logger);

            /** @var \Steelbot\UserInterface $user */
            $user = $this->getMock(\Steelbot\UserInterface::class);
            $payload = new TextMessage('a', $user, $user);

            $matcher = new PcreRouteMatcher('~a~');
            $handler = 'somethingUnexistent';

            $contextProvider->addRoute($matcher, $handler);

            $this->setExpectedException(\UnexpectedValueException::class);
            $foundHandler = $contextProvider->findContext($payload, $user);
            $this->assertSame($handler, $foundHandler);
        }
    }

}
