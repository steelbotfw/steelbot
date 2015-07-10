<?php

namespace Steelbot\Protocol\Shell;

use Steelbot\ClientInterface;

/**
 * Class Client
 * @package Steelbot\Protocol\Shell
 */
class Client implements ClientInterface
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'stdin';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }
}