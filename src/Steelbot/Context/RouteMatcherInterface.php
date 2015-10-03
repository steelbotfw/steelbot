<?php

namespace Steelbot\Context;

interface RouteMatcherInterface
{
    /**
     * @param $payload
     *
     * @return bool
     */
    public function match($payload) : bool;
}