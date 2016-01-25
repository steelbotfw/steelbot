<?php

namespace Steelbot\Protocol\Gitter;

use Steelbot\ClientInterface;

/**
 * Class Client
 */
class Client implements ClientInterface
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }
}
