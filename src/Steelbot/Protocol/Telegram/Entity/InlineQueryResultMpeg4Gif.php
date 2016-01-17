<?php

namespace Steelbot\Protocol\Telegram\Entity;

class InlineQueryResultMpeg4Gif
{
    public $type;

    public $id;

    public $mpeg4Url;

    public $mpeg4Width;

    public $mpeg4Height;

    public $thumbUrl;

    public $title;

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
        $this->mpeg4Url = $data['mpeg4_url'];
        $this->mpeg4Width = $data['mpeg4_width'] ?? null;
        $this->mpeg4Height = $data['mpeg4_height'] ?? null;
        $this->thumbUrl = $data['thumb_url'];
        $this->title = $data['title'] ?? null;
        $this->caption = $data['caption'] ?? null;
        $this->messageText = $data['message_text'] ?? null;
        $this->parseMode = $data['parse_mode'] ?? null;
        $this->disableWebPagePreview = $data['disable_web_page_preview'] ?? null;
    }
}
