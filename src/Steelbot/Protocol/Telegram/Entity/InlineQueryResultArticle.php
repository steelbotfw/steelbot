<?php

namespace Steelbot\Protocol\Telegram\Entity;

class InlineQueryResultArticle
{
    public $type;

    public $id;

    public $title;

    public $messageText;

    public $parseMode;

    public $disableWebPagePreview;

    public $url;

    public $hideUrl;

    public $description;

    public $thumbUrl;

    public $thumbWidth;

    public $thumbHeight;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->type = $data['type'];
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->messageText = $data['message_text'];
        $this->parseMode = $data['parse_mode'];
        $this->disableWebPagePreview = $data['disable_web_page_preview'] ?? null;
        $this->url = $data['url'] ?? null;
        $this->hideUrl = $data['hide_url'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->thumbUrl = $data['thumb_url'] ?? null;
        $this->thumbWidth = $data['thumb_width'] ?? null;
        $this->thumbHeight = $data['thumb_height'] ?? null;
    }
}
