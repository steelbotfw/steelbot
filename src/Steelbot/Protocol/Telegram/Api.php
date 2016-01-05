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
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @param string $token
     */
    public function __construct(string $token, LoggerInterface $logger = null)
    {
        $this->token = $token;
        $this->httpClient = new Client();
        $this->logger = $logger;

        $urlInfo = parse_url($this->baseUrl);
        $this->host = $urlInfo['host'];
        switch ($urlInfo['scheme']) {
            case 'http':
                $this->port = 80;
                break;
            case 'https':
                $this->port = 443;
                break;
            default:
                throw new \DomainException("Unkown scheme");
        }
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
        $response = yield from $this->get('/getMe');

        $body = yield from $this->getResponseBody($response);
        $body = json_decode($body, true);
        return new Entity\User($body['result']);
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

        $response = yield from $this->post('/sendMessage', $params);

        $body = yield from $this->getResponseBody($response);
        $body = json_decode($body, true);

        return new Entity\Message($body['result']);
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

        $body = yield $this->getResponseBody($response);
        $body = json_decode($body, true);

        yield new Entity\Message($body['result']);
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
            static::ACTION_TYPING,       // for text messages,
            static::ACTION_UPLOAD_PHOTO, // for photos,
            static::ACTION_RECORD_VIDEO,
            static::ACTION_UPLOAD_VIDEO,
            static::ACTION_RECORD_AUDIO,
            static::ACTION_UPLOAD_AUDIO,
            static::ACTION_UPLOAD_DOCUMENT,
            static::ACTION_FIND_LOCATION
        ];

        $response = yield $this->post('/sendChatAction', [
            'chat_id' => $chatId,
            'action' => $action
        ]);
        $body = yield $this->getResponseBody($response);
        $body = json_decode($body, true);

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
        $response = yield from $this->post('/getUpdates', [
            'offset'  => $lastUpdateId + 1,
            'limit'   => $limit,
            'timeout' => $timeout
        ]);

        $updates = [];

        if ($response->getStatusCode() == 200) {
            $body = yield from $this->getResponseBody($response);

            $this->logger && $this->logger->debug("Data received", ['length' => strlen($body)]);

            $updatesData = json_decode($body, JSON_UNESCAPED_UNICODE);

            foreach ($updatesData['result'] as $updateData) {
                $updates[] = new Update($updateData);
            }

        } else {
            $this->logger->error("Response http error: ".$response->getStatusCode());
        }

        return $updates;
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
    protected function get(string $pathName, array $params = []): \Generator
    {
        $this->logger && $this->logger->debug("Requesting $pathName", $params);
        $url = $this->buildUrl($pathName, $params);

        return $this->request('GET', $url, [], null, [
            'timeout' => 60
        ]);
    }

    /**
     * @param string $pathName
     * @param array $params
     *
     * @yield Generator
     */
    protected function post(string $pathName, array $params = []): \Generator
    {
        $this->logger && $this->logger->debug("Requesting $pathName", $params);
        $url = $this->buildUrl($pathName, $params);

        return $this->request('POST', $url, [], null, [
            'timeout' => 60
        ]);
    }

    /**
     * @param string $method
     * @param        $uri
     * @param array  $headers
     * @param null   $body
     * @param array  $options
     *
     * @return \Generator
     */
    protected function request(string $method, $uri, array $headers = [], $body = null, array $options = []): \Generator
    {
        $socket = (yield \Icicle\Dns\connect($this->host, $this->port));
        return (yield $this->httpClient->request($socket, $method, $uri, $headers, $body, $options));
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
        $nonEmptyParams = array_filter($params, function ($value) { return $value !== null; });
        $paramStr = count($nonEmptyParams) ? '?'.http_build_query($nonEmptyParams) : null;

        return $this->baseUrl.$this->token.$pathName.$paramStr;
    }

    /**
     * @param Response $response
     *
     * @return \Generator
     *
     * @resolve string
     */
    protected function getResponseBody(Response $response): \Generator
    {
        $data = '';
        $stream = $response->getBody();
        while ($stream->isReadable()) {
            $data .= yield $stream->read();
        }

        return $data;
    }
}
