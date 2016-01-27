<?php

namespace Steelbot\Protocol;

interface TextMessageInterface
{
    /**
     * @return string
     */
    public function getText(): string;
}
