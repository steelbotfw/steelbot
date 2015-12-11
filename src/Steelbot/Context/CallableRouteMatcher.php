<?php

namespace Steelbot\Context;

use Steelbot\Protocol\IncomingPayloadInterface;

class CallableRouteMatcher extends AbstractRouteMatcher
{
    protected $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @param $payload
     *
     * @return bool
     */
    public function match(IncomingPayloadInterface $payload): bool
    {
        return call_user_func_array($this->callable, [$payload]);
    }
}
