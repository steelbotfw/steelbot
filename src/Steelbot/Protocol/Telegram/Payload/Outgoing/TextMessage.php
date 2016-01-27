<?php

namespace Steelbot\Protocol\Telegram\Payload\Outgoing;

/**
 * Outgoing text message
 */
class TextMessage extends \Steelbot\Protocol\Payload\Outgoing\TextMessage
{
    const PARSE_MODE_MARKDOWN = 'Markdown';
    const PARSE_MODE_HTML = 'HTML';

    /**
     * @var string|null
     */
    protected $parseMode;

    /**
     * @todo
     *
     * @var
     */
    protected $disableWebPagePreview = false;

    /**
     * @todo
     *
     * @var
     */
    protected $replyToMessageId = null;

    /**
     * @todo
     *
     * @var
     */
    protected $replyMarkup = null;

    /**
     * @return string|null
     */
    public function getParseMode()
    {
        return $this->parseMode;
    }

    /**
     * @param string|null $parseMode
     */
    public function setParseMode($parseMode)
    {
        $this->parseMode = $parseMode;
    }

    /**
     * @return mixed
     */
    public function getDisableWebPagePreview()
    {
        return $this->disableWebPagePreview;
    }

    /**
     * @param mixed $disableWebPagePreview
     */
    public function setDisableWebPagePreview(bool $disableWebPagePreview)
    {
        $this->disableWebPagePreview = $disableWebPagePreview;
    }

    /**
     * @return mixed
     */
    public function getReplyToMessageId()
    {
        return $this->replyToMessageId;
    }

    /**
     * @param mixed $replyToMessageId
     */
    public function setReplyToMessageId($replyToMessageId)
    {
        $this->replyToMessageId = $replyToMessageId;
    }

    /**
     * @return mixed
     */
    public function getReplyMarkup()
    {
        return $this->replyMarkup;
    }

    /**
     * @param mixed $replyMarkup
     */
    public function setReplyMarkup($replyMarkup)
    {
        $this->replyMarkup = $replyMarkup;
    }


}
