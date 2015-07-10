<?php

namespace Steelbot;

/**
 * Interface ClientInterface
 * @package Steelbot
 */
interface ClientInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function __toString();
} 