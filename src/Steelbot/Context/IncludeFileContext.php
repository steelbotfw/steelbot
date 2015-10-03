<?php

namespace Steelbot\Context;

use Steelbot\Application;
use Steelbot\ClientInterface;

class IncludeFileContext extends Context
{
    /**
     * @var string
     */
    protected $includeFile;

    /**
     * @param \Steelbot\Application $app
     * @param \Steelbot\ClientInterface $client
     */
    public function __construct(Application $app, ClientInterface $client, string $includeFile)
    {
        parent::__construct($app, $client);
        $this->includeFile = $includeFile;
    }

    /**
     * @param $message
     *
     * @return bool
     */
    public function handle($message)
    {
        include $this->includeFile;

        $this->resolve();
        return true;
    }
}