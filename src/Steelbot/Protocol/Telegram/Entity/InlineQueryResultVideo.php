<?php

namespace Steelbot\Protocol\Telegram\Entity;

class InlineQueryResultVideo
{
    public $type;

    public $id;

    public $videoUrl;

    public $mimeType;

    public $messageText;

    public $parseMode;

    public $disableWebPagePreview;

    public $videoWidth;

    public $videoHeight;

    public $videoDuration;

    public $thumbUrl;

    public $title;

    public $description;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->type = $data['type'];
        $this->id = $data['id'];
        $this->videoUrl = $data['video_url'];
        $this->mimeType = $data['mime_type'];
        $this->messageText = $data['message_text'];
        $this->parseMode = $data['parse_mode'] ?? null;
        $this->disableWebPagePreview = $data['disable_web_page_preview'] ?? null;
        $this->videoWidth = $data['video_width'] ?? null;
        $this->videoHeight = $data['video_height'] ?? null;
        $this->videoDuration = $data['video_duration'] ?? null;
        $this->thumb_url = $data['thumb_url'];
        $this->title = $data['title'];
        $this->description = $data['description'] ?? null;
    }
}
