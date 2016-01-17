<?php

namespace Steelbot\Protocol\Telegram\Entity;

class InlineQueryResultGif
{
    public $type;

    public $id;

    public $gifUrl;

    public $gifWidth;

    public $gifHeight;

    public $thumbUrl;

    public $title;

    public $caption;

    public $messageText;

    public $parseMode;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->type = $data['type'];
        $this->id = $data['id'];
        $this->gifUrl = $data['gif_url'];
        $this->gifWidth = $data['gif_width'] ?? null;
        $this->gifHeight = $data['gif_height'] ?? null;
        $this->thumbUrl = $data['thumb_url'];
        $this->title = $data['title'] ?? null;
        $this->caption = $data['caption'] ?? null;
        $this->messageText = $data['message_text'] ?? null;
        $this->parseMode = $data['parse_mode'] ?? null;
        $this->disableWebPagePreview = $data['disable_web_page_preview'] ?? null;
    }
}
