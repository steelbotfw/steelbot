<?php

namespace Steelbot;

use Psr\Log\LoggerInterface;
use Monolog;
use Icicle\Coroutine;
use Icicle\Loop;
use Steelbot\Context\ContextProviderCompilerPass;
use Steelbot\Event\AfterBootEvent;
use Steelbot\Protocol\Event\IncomingPayloadEvent;
use Steelbot\Exception\ContextNotFoundException;
use Steelbot\Protocol\Payload\Incoming\AbstractMessage;
use Steelbot\Protocol\Payload\Outgoing\TextMessage;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class Application
 *
 * @package Steelbot
 */
class Application extends Kernel
{
    /**
     * @var bool
     */
    protected $loadClassCache = false;

    /**
     * @var array
     */
    protected $configs = [];

    /**
     * @return bool
     */
    public function registerPayloadHandler(): bool
    {
        $eventCallback = function (IncomingPayloadEvent $event) {
            $payload = $event->getPayload();

            if ($payload instanceof AbstractMessage) {
                $this->getLogger()->info("Received message payload.", [
                    'from'    => $payload->getFrom()->getId(),
                    'content' => (string)$payload
                ]);

                $callback = function ($payload): \Generator 
                {
                    try {
                        yield from $this->getContextRouter()->handle($payload);
                    } catch (ContextNotFoundException $e) {
                        $this->getLogger()->debug("Handle not found");

                        if (!$payload->isGroupChatMessage()) {
                            yield from $this->getProtocol()->send($payload->getFrom(), new TextMessage("Command not found"));
                        }
                    }
                };

                $coroutine = Coroutine\create($callback, $payload);
                $coroutine->done(null, function (\Throwable $e) {
                    $this->getLogger()->error("Throwable catched", [
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                        'trace'   => $e->getTraceAsString()
                    ]);
                });
            }
        };

        $this->getEventDispatcher()->addListener(IncomingPayloadEvent::NAME, $eventCallback);
        return true;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->container->get('logger');
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->container->get('event_dispatcher');
    }

    /**
     * @return \Steelbot\Protocol\AbstractProtocol
     */
    public function getProtocol(): \Steelbot\Protocol\AbstractProtocol
    {
        return $this->container->get('protocol');
    }

    /**
     * @return \Steelbot\ContextRouter
     */
    public function getContextRouter(): \Steelbot\ContextRouter
    {
        return $this->container->get('context_router');
    }

    /**
     * Start steelbot
     */
    public function run()
    {
        echo "Steelbot 4.0.0-dev\n\n";

        $this->boot();
        $this->getEventDispatcher()->dispatch(AfterBootEvent::NAME);

        $coroutine = Coroutine\create(function() {
            yield from $this->getProtocol()->connect();
        });
        $coroutine->done(null, function(\Throwable $e) {
            $this->getLogger()->error("Throwable catched", [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        });

        $this->registerPayloadHandler();

        Loop\run();
    }

    /**
     * Stop steelbot
     */
    public function stop()
    {
        if (Loop\isRunning()) {
            Loop\stop();
        }
    }

    /**
     * Add application config.
     *
     * @param string|\Closure $config config filename or callback
     */
    public function addConfig($config)
    {
        $this->configs[] = $config;
    }

    /**
     * Returns an array of bundles to register.
     *
     * @return BundleInterface[] An array of bundle instances.
     */
    public function registerBundles()
    {
        return [];
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function buildContainer()
    {
        $container = parent::buildContainer();

        $container->addCompilerPass(new ContextProviderCompilerPass());
        $container->addCompilerPass(new RegisterListenersPass(), PassConfig::TYPE_BEFORE_REMOVING);

        return $container;
    }
}
