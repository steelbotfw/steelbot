<?php

namespace Steelbot\Protocol\Shell;

use Steelbot\ClientInterface;

/**
 * Class Client
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
