<?php

namespace Steelbot\Protocol\Telegram\Entity;

class InlineQueryResultPhoto
{
    public $type;

    public $id;

    public $photoUrl;

    public $photoWidth;

    public $photoHeight;

    public $thumbUrl;

    public $title;

    public $description;

    public $caption;

    public $messageText;

    public $parseMode;

    public $disableWebPagePreview;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->type = $data['type'];
        $this->id = $data['id'];
        $this->photoUrl = $data['photo_url'];
        $this->photoWidth = $data['photo_width'] ?? null;
        $this->photoHeight = $data['photo_height'] ?? null;
        $this->thumbUrl = $data['thumb_url'];
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->caption = $data['caption'] ?? null;
        $this->messageText = $data['message_text'] ?? null;
        $this->parseMode = $data['parse_mode'] ?? null;
        $this->disableWebPagePreview = $data['disable_web_page_preview'] ?? null;
    }
}
