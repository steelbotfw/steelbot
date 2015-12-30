<?php

namespace Steelbot\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class AfterBootEvent
 * @package Steelbot\Event
 */
class AfterBootEvent extends Event
{
    const NAME = self::class;
}
