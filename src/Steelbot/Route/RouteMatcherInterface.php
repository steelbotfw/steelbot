<?php

namespace Steelbot\Route;

use Steelbot\Protocol\IncomingPayloadInterface;

interface RouteMatcherInterface
{
    /**
     * @param $payload
     *
     * @return bool
     */
    public function match(IncomingPayloadInterface $payload): bool;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @return array
     */
    public function getHelp(): array;
}
