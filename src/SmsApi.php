<?php

namespace NotificationChannels\SmsBee;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use NotificationChannels\SmsBee\Exceptions\CouldNotSendNotification;

class SmsApi
{
    public const FORMAT_JSON = 3;

    /** @var HttpClient */
    protected $client;

    /** @var string */
    protected $endpoint = 'https://a2p-sms-https.beeline.ru/proto/http/';

    /** @var string */
    protected $login;

    /** @var string */
    protected $secret;

    /** @var string */
    protected $sender;

    /** @var array */
    protected $extra;

    protected $action = 'post_sms';
    protected $charset = 'utf-8';
    protected $headers = ['headers' => [
        'Content-Encoding' => 'gzip',
        'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
    ]];

    public function __construct(array $config)
    {
        $this->action = Arr::get($config, 'action', $this->action);
        $this->charset  = Arr::get($config, 'charset ', $this->charset);
        $this->login = Arr::get($config, 'login');
        $this->secret = Arr::get($config, 'secret');
        $this->sender = Arr::get($config, 'sender');
        $this->endpoint = Arr::get($config, 'host',  $this->endpoint);

        $this->extra = Arr::get($config, 'extra', []);

        $this->setContentTypeEncoding($this->charset);

        $this->client = new HttpClient([
            'timeout' => 5,
            'connect_timeout' => 5,
        ]);
    }

    protected function setContentTypeEncoding(string $encoding = 'utf-8')
    {
        $contentType = Arr::get($this->headers, 'headers.Content-Type', $this->headers) . $encoding;
        Arr::set($this->headers, 'headers.Content-Type', $contentType);
    }


    /**
     * @throws CouldNotSendNotification
     * @throws GuzzleException
     */
    public function send($params)
    {
        $base = [
            "action" => $this->action,
            'user' => $this->login,
            'pass' => $this->secret,
            'sender' => $this->sender,
        ];

        $params['form_params'] = \array_merge($base, \array_filter($params));

        $params = \array_merge($params, $this->extra, $this->headers);

        try {
            $response = $this->client->request('POST', $this->endpoint, $params);

            $response = \json_decode((string)$response->getBody(), true);

            if (isset($response['error'])) {
                throw new \DomainException($response['error'], $response['error_code']);
            }

            return $response;
        } catch (\DomainException $exception) {
            throw CouldNotSendNotification::smscRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithSmsc($exception);
        }
    }
}
