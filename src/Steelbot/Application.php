<?php

namespace Steelbot;

use Psr\Log\LoggerInterface;
use Monolog;
use Icicle\Coroutine;
use Icicle\Loop;
use Steelbot\Exception\ContextNotFoundException;
use Steelbot\Protocol\IncomingPayloadInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class Application
 *
 * @package Steelbot
 */
class Application
{
    const ENV_DEV = 'dev';
    const ENV_STAGING = 'staging';
    const ENV_PROD = 'prod';

    /**
     * @var \SplObjectStorage
     */
    protected $modules;

    /**
     * @var string
     */
    protected $env;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @param string $env
     */
    public function __construct()
    {
        $this->setEnv(STEELBOT_ENV);

        $this->container = new ContainerBuilder();
        $this->modules = new \SplObjectStorage();
    }

    /**
     * @return bool
     */
    public function registerPayloadHandler(): bool
    {
        $wrap = Coroutine\wrap(function (IncomingPayloadInterface $payload) {
            $this->getLogger()->info("Received payload.", [
                'from' => $payload->getFrom()->getId(),
                'content' => (string)$payload
            ]);

            try {
                yield $this->getContextRouter()->handle($payload);
            } catch (ContextNotFoundException $e) {
                $this->getLogger()->debug("Handle not found");

                if (!$payload->isGroupChatMessage()) {
                    yield $this->getProtocol()->send($payload->getFrom(), "Command not found");
                }
            }
        }, []);

        $this->getEventEmitter()->on(\Steelbot\Protocol\AbstractProtocol::EVENT_PAYLOAD_RECEIVED, $wrap);

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
     * @return EventEmitter
     */
    public function getEventEmitter(): EventEmitter
    {
        return $this->container->get('event_emitter');
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
        echo "Steelbot 4.0-dev\n\n";

        $this->container->set('event_emitter', new EventEmitter());

        $logger = new Monolog\Logger('logger');
        $logger->setHandlers([
            'main' => new Monolog\Handler\ErrorLogHandler()
        ]);
        $this->container->set('logger', $logger);

        $contextRouter = new \Steelbot\ContextRouter($this);
        $contextRouter->setLogger($this->container->get('logger'));
        $this->container->set('context_router', $contextRouter);

        $ymlLoader = new YamlFileLoader($this->container, new FileLocator(APP_DIR));
        $ymlLoader->load('config.yml');

        foreach ($this->modules as $module) {
            $module->init();
        }

        $coroutine = Coroutine\create(function() {
            yield $this->getProtocol()->connect();
        });
        $coroutine->done(null, function (\Exception $e) {
            printf("Exception: %s\n", $e);
        });

        // initialize protocol events
        $this->getProtocol();
        
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
     * @param string $env
     *
     * @return bool
     */
    public function setEnv(string $env): bool
    {
        $this->env = $env;

        return true;
    }

    /**
     * @return string
     */
    public function getEnv(): string
    {
        return $this->env;
    }

    /**
     * @param string $moduleClass
     */
    public function addModule(string $moduleClass): self
    {
        $module = new $moduleClass($this);
        $this->modules->attach($module);

        return $this;
    }
}
