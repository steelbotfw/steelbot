<?php

namespace Steelbot\Context;

interface ContextInterface
{
    public function handle($payload);

    /**
     * @return boolean
     */
    public function isResolved() : bool;
}