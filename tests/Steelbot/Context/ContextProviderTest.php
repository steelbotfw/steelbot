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

        public function testGetRoutes()
        {
            $contextProvider = new ContextProvider();
            $this->assertCount(0, $contextProvider->getRoutes());

            $matcher = new PcreRouteMatcher('~a~');
            $handler = function () {
                return true;
            };

            $matcher2 = new PcreRouteMatcher('~b~');

            $contextProvider->setRoute($matcher, $handler);
            $contextProvider->setRoute($matcher2, $handler);

            $getRoutes = $contextProvider->getRoutes();
            $this->assertCount(2, $getRoutes);
            $this->assertSame([[$matcher, $handler], [$matcher2, $handler]], $getRoutes);
        }

        public function testFindContextNotFound()
        {
            $environment = 'test';
            $contextProvider = new ContextProvider();
            $logger = $this->getMock('Psr\Log\LoggerInterface');
            $contextProvider->setLogger($logger);

            $payload = $this->getMock('Steelbot\Protocol\IncomingPayloadInterface');
            $client = $this->getMock('Steelbot\ClientInterface');
            $app = new Application($environment, true);

            $this->assertFalse($contextProvider->findContext($payload, $client, $app));
        }

        public function testFindContextCallableFound()
        {
            $environment = 'test';
            $contextProvider = new ContextProvider();
            $logger = $this->getMock('Psr\Log\LoggerInterface');
            $contextProvider->setLogger($logger);

            /** @var \Steelbot\ClientInterface $client */
            $client = $this->getMock('Steelbot\ClientInterface');
            $payload = new TextMessage('a', $client, $client);
            $app = new Application($environment, true);

            $matcher = new PcreRouteMatcher('~a~');
            $handler = function () {
                return true;
            };
            $help = [
                'a' => 'Help text'
            ];
            $contextProvider->setRoute($matcher, $handler, $help);

            $container = $this->getMockBuilder(ContainerInterface::class)
                ->getMock();
            $contextProvider->setContainer($container);

            $foundHandler = $contextProvider->findContext($payload, $client, $container);

            $this->assertSame($handler, $foundHandler);
        }

        public function testFindContextClassFound()
        {
            $contextProvider = new ContextProvider();
            $logger = $this->getMock('Psr\Log\LoggerInterface');
            $contextProvider->setLogger($logger);

            /** @var \Steelbot\ClientInterface $client */
            $client = $this->getMock('Steelbot\ClientInterface');
            $payload = new TextMessage('a', $client, $client);

            $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
            $protocol = $this->getMockForAbstractClass(AbstractProtocol::class, [$eventDispatcher]);

            $container = $this->getMockBuilder(ContainerInterface::class)
                ->getMock();
            $container->method('get')
                ->with('protocol')
                ->will($this->returnValue($protocol));
            $contextProvider->setContainer($container);

            $matcher = new PcreRouteMatcher('~a~');

            $handler = $this->getMockForAbstractClass(AbstractContext::class, [$protocol, $client]);
            $help = [
                'a' => 'Help text'
            ];
            $contextProvider->setRoute($matcher, get_class($handler), $help);

            $foundHandler = $contextProvider->findContext($payload, $client);
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

            $logger = $this->getMock('Psr\Log\LoggerInterface');
            $contextProvider->setLogger($logger);

            /** @var \Steelbot\ClientInterface $client */
            $client = $this->getMock('Steelbot\ClientInterface');
            $payload = new TextMessage('a', $client, $client);

            $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
            $protocol = $this->getMockForAbstractClass(AbstractProtocol::class, [$eventDispatcher]);

            $container = $this->getMockBuilder(ContainerInterface::class)
                ->getMock();
            $container->method('get')
                ->with('protocol')
                ->will($this->returnValue($protocol));
            $contextProvider->setContainer($container);

            $matcher = new PcreRouteMatcher('~a~');
            $handlerFilename = 'handler.php';
            $help = [
                'a' => 'Help text'
            ];
            $contextProvider->setRoute($matcher, $handlerFilename, $help);

            $foundHandler = $contextProvider->findContext($payload, $client);
            $this->assertSame($handler, $foundHandler);
        }

        public function testFindContextErrorResolving()
        {
            global $mockFileExists;
            $mockFileExists = false;

            $contextProvider = new ContextProvider();

            $logger = $this->getMock('Psr\Log\LoggerInterface');
            $contextProvider->setLogger($logger);

            /** @var \Steelbot\ClientInterface $client */
            $client = $this->getMock('Steelbot\ClientInterface');
            $payload = new TextMessage('a', $client, $client);

            $matcher = new PcreRouteMatcher('~a~');
            $handler = 'somethingUnexistent';

            $contextProvider->setRoute($matcher, $handler);

            $this->setExpectedException(\UnexpectedValueException::class);
            $foundHandler = $contextProvider->findContext($payload, $client);
            $this->assertSame($handler, $foundHandler);
        }
    }

}
