<?php

namespace Steelbot\Protocol\Telegram;

use Icicle\Http\Client\Client;
use Icicle\Http\Message\Request;
use Icicle\Http\Message\Response;
use Icicle\Stream\MemoryStream;
use Icicle\Stream\pipe;
use Icicle\Stream\Pipe\ReadablePipe;
use Psr\Log\LoggerInterface;
use Steelbot\Protocol\Telegram\Entity\Update;
use Steelbot\Protocol\Telegram\Entity;

/**
 * Class Api
 *
 * @package Telegram
 *
 * @see https://core.telegram.org/bots/api#available-methods
 */
class Api
{
    const ACTION_TYPING = 'typing';
    const ACTION_UPLOAD_PHOTO = 'upload_photo';
    const ACTION_RECORD_VIDEO = 'record_video';
    const ACTION_UPLOAD_VIDEO = 'upload_video';
    const ACTION_RECORD_AUDIO = 'record_audio';
    const ACTION_UPLOAD_AUDIO = 'upload_audio';
    const ACTION_UPLOAD_DOCUMENT = 'upload_document';
    const ACTION_FIND_LOCATION = 'find_location';

    /**
     * @var string
     */
    private $baseUrl = 'https://api.telegram.org/bot';

    /**
     * @var string
     */
    private $token;

    /**
     * @var \Icicle\Http\Client\Client
     */
    private $httpClient;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param string $token
     */
    public function __construct(string $token, LoggerInterface $logger = null)
    {
        $this->token = $token;
        $this->httpClient = new Client();
        $this->logger = $logger;
    }

    /**
     * @see https://core.telegram.org/bots/api#getme
     *
     * @coroutine
     *
     * @return \Generator
     * @resolve Entity\User
     */
    public function getMe() : \Generator
    {
        /** @var Response $response */
        $response = yield $this->get('/getMe');

        $stream = $response->getBody();
        $data = '';
        while ($stream->isReadable()) {
            $data .= yield $stream->read();
        }

        $data = json_decode($data, true);

        yield new Entity\User($data['result']);
    }

    /**
     * Send message to a user
     *
     * @see https://core.telegram.org/bots/api#sendmessage
     *
     * @coroutine
     *
     * @param int $chatId
     * @param string $text
     * @param bool $disableWebPagePreview
     * @param int null $replyToMessageId
     * @param null $replyMarkup
     *
     * @return \Generator
     * @resolve Entity\Message
     */
    public function sendMessage(int    $chatId,
                                string $text,
                                string $parseMode,
                                bool   $disableWebPagePreview = false,
                                int    $replyToMessageId = null,
                                string $replyMarkup = null): \Generator
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text
        ];

        if ($parseMode) {
            $params['parse_mode'] = $parseMode;
        }

        if ($disableWebPagePreview) {
            $params['disable_web_page_preview'] = true;
        }
        if ($replyToMessageId) {
            $params['reply_to_message_id'] = $replyToMessageId;
        }
        if ($replyMarkup) {
            $params['reply_markup'] = $replyMarkup;
        }

        $response = yield $this->post('/sendMessage', $params);

        $stream = $response->getBody();
        $data = '';
        while ($stream->isReadable()) {
            $data .= yield $stream->read();
        }

        $data = json_decode($data, true);

        yield new Entity\Message($data['result']);
    }

    /**
     * @todo
     */
    public function forwardMessage(): \Generator
    {

    }

    /**
     * Send image to a user
     *
     * @var int         $chatId
     * @var resource    $photo
     * @var string|null $caption
     * @var int|null    $replyToMessageId
     * @var mixed       $replyMarkup
     *
     * @return \Generator
     */
    public function sendPhoto(int    $chatId,
                                     $photo,
                              string $caption = null,
                              int    $replyToMessageId = null,
                                     $replyMarkup = null
                                    ): \Generator
    {
        $params = [
            'chat_id' => $chatId,
            'caption' => $caption,
            'replyToMessageId' => $replyToMessageId
        ];

        $imageContentPipe = new ReadablePipe($photo);

        $boundary = uniqid();

        $mem = new MemoryStream();
        $contentLength = 0;
        $contentLength += yield $mem->write("--$boundary\r\nContent-Disposition: form-data; name=\"photo\"; filename=\"example1.jpg\"\r\n\r\n");
        $contentLength += yield pipe($imageContentPipe, $mem, false);
        $contentLength += yield $mem->end("\r\n--$boundary--");

        $url = $this->buildUrl('/sendPhoto', $params);

        $headers = [
            'Content-type' => "multipart/form-data, boundary=$boundary",
            'Content-Length' => $contentLength
        ];

        $request = new Request('POST', $url, $headers, $mem);

        $response = yield $this->httpClient->send($request, []);

        $stream = $response->getBody();
        $data = '';
        while ($stream->isReadable()) {
            $data .= yield $stream->read();
        }
        $data = json_decode($data, true);

        yield new Entity\Message($data['result']);
    }

    /**
     * @todo
     */
    public function sendAudio()
    {

    }

    /**
     * @todo
     */
    public function sendDocument()
    {

    }

    /**
     * @todo
     */
    public function sendSticker()
    {

    }

    /**
     * @todo
     */
    public function sendVideo()
    {

    }

    /**
     * @todo
     */
    public function sendVoice()
    {

    }

    /**
     * @todo
     */
    public function sendLocation()
    {

    }

    /**
     * Send chat action.
     *
     * @coroutine
     *
     * @param int $chatId
     * @param string $action
     *
     * @return \Generator
     * @resolve true
     */
    public function sendChatAction(int $chatId, string $action): \Generator
    {
        $actions = [
            'typing', // for text messages,
            'upload_photo', // for photos,
            'record_video',
            'upload_video',
            'record_audio',
            'upload_audio',
            'upload_document',
            'find_location'
        ];

        $response = yield $this->post('/sendChatAction', [
            'chat_id' => $chatId,
            'action' => $action
        ]);

        $stream = $response->getBody();
        $data = '';
        while ($stream->isReadable()) {
            $data .= yield $stream->read();
        }

        $data = json_decode($data, true);

        yield true;
    }

    /**
     * @todo
     */
    public function getUserProfilePhotos()
    {

    }

    /**
     * @param int $lastUpdateId
     * @param int $limit
     * @param int $timeout
     *
     * @see
     *
     * @return \Generator
     * @resolve Update[]
     */
    public function getUpdates(int $lastUpdateId, int $limit = 5, int $timeout = 30) : \Generator
    {
        /** @var Response $response */
        $response = yield $this->post('/getUpdates', [
            'offset'  => $lastUpdateId + 1,
            'limit'   => $limit,
            'timeout' => $timeout
        ]);

        if ($response->getStatusCode() == 200) {
            $data = null;

            $stream = $response->getBody();
            while ($stream->isReadable()) {
                $data .= yield $stream->read();
            }

            $this->logger && $this->logger->debug("Data received", ['length' => strlen($data)]);

            $updates = json_decode($data, JSON_UNESCAPED_UNICODE);
            $collection = [];

            foreach ($updates['result'] as $updateData) {
                $collection[] = new Update($updateData);
            }

            yield $collection;

        } else {
            $this->logger->error("Response http error: ".$response->getCode());
        }
    }

    /**
     * @todo
     */
    public function getFile()
    {

    }

    /**
     * @param string $url
     * @param array $params
     *
     * @yield Generator
     */
    protected function get(string $pathName, array $params = [])
    {
        $this->logger && $this->logger->debug("Requesting $pathName", $params);

        $url = $this->buildUrl($pathName, $params);

        yield $this->httpClient->request('GET', $url, [], null, [
            'timeout' => 60
        ]);
    }

    /**
     * @param string $pathName
     * @param array $params
     *
     * @yield Generator
     */
    protected function post(string $pathName, array $params = [])
    {
        $this->logger && $this->logger->debug("Requesting $pathName", $params);

        $url = $this->buildUrl($pathName, $params);

        yield $this->httpClient->request('POST', $url, [], null, [
            'timeout' => 60
        ]);
    }

    /**
     * Build full URL to a telegram API with given pathName
     *
     * @param string $pathName
     *
     * @return string
     */
    protected function buildUrl(string $pathName, array $params = []): string
    {
        $nonEmptyParams = array_filter($params, function ($value) { $value === null; });
        $paramStr = count($nonEmptyParams) ? '?'.http_build_query($nonEmptyParams) : null;

        return $this->baseUrl.$this->token.$pathName.$paramStr;
    }
}