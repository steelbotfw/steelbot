<?php

namespace Steelbot\Protocol\Telegram;

use Steelbot\Protocol\IncomingPayloadInterface;
use Steelbot\Protocol\Telegram\Entity\Update;
use Steelbot\Protocol\Payload\Incoming\LocationMessage;
use Steelbot\Protocol\Payload\Incoming\TextMessage;

/**
 * Class IncomingPayload
 */
class IncomingPayload implements IncomingPayloadInterface
{
    /**
     * @var \Steelbot\Protocol\Telegram\Entity\Update
     */
    private $update;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Steelbot\Protocol\Telegram\Entity\Message
     */
    private $message;

    /**
     * @param \Steelbot\Protocol\Telegram\Entity\Update $update
     */
    public function __construct(Update $update)
    {
        $this->update = $update;
        if (!empty($this->update->message->text)) {
            $this->type = self::TYPE_TEXT;
        } elseif (!empty($this->update->message->location)) {
            $this->type = self::TYPE_LOCATION;
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Message factory.
     *
     * @return LocationMessage|TextMessage
     */
    public function getMessage()
    {
        if ($this->message) {
            return $this->message;
        }

        switch ($this->type) {
            case self::TYPE_TEXT:
                $this->message = new TextMessage(
                    $this->update->message->text,
                    $this->update->message->chat,
                    $this->update->message->from
                );
                break;

            case self::TYPE_LOCATION:
                $this->message = new LocationMessage(
                    $this->update->message->location->longitude,
                    $this->update->message->location->latitude,
                    $this->update->message->chat,
                    $this->update->message->from
                );
                break;

            default:
                throw new \DomainException("Unknown payload type");
        }

        return $this->message;
    }

    public function __toString(): string
    {
        return (string)$this->getMessage();
    }
}
