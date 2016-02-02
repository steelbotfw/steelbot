<?php

namespace Steelbot\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class BeforeStopEvent
 */
class BeforeStopEvent extends Event
{
    const NAME = self::class;
}
