<?php

namespace Steelbot\Protocol\Telegram\Payload\Incoming;

use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\UserInterface;

/**
 * Class AbstractMessage
 *
 */
class InlineQuery implements IncomingPayloadInterface
{
    /**
     * @var UserInterface
     */
    protected $from;

    protected $id;

    protected $query;

    protected $offset;

    /**
     * @param \Steelbot\UserInterface $from
     * @param $id
     * @param $query
     * @param $offset
     */
    public function __construct(UserInterface $from, $id, $query, $offset)
    {
        $this->from = $from;
        $this->id = $id;
        $this->query = $query;
        $this->offset = $offset;
    }

    /**
     * @return \Steelbot\UserInterface
     */
    public function getFrom(): UserInterface
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->query;
    }

    /**
     * Payload type.
     *
     * @return string
     */
    public function getType(): string
    {
        // TODO: Implement getType() method.
    }
}
