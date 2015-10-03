<?php

namespace Steelbot;

class EventEmitter extends \Icicle\EventEmitter\EventEmitter
{
    /**
     * @param string $name
     *
     * @return $this
     */
    public function addEvent(string $name)
    {
        return self::createEvent($name);
    }

    public function emit($event, array $args = [])
    {
        return parent::emit($event, ...$args);
    }
}