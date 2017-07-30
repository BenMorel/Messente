<?php

namespace Messente;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

/**
 * Sends SMS messages using the Messente API.
 */
class Messente
{
    const PRIMARY_API = 'https://api2.messente.com';
    const BACKUP_API  = 'https://api3.messente.com';

    const SEND_SMS_ENDPOINT = '/send_sms/';

    /**
     * The Guzzle HTTP client.
     *
     * @var Client
     */
    private $client;

    /**
     * The API username.
     *
     * @var string
     */
    private $username;

    /**
     * The API password.
     *
     * @var string
     */
    private $password;

    /**
     * Whether to use the backup API.
     *
     * @var bool
     */
    private $useBackupApi = false;

    /**
     * Class constructor.
     *
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->client = new Client();

        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Sets whether to use the backup API.
     *
     * This can be used when the primary API is down for any reason.
     *
     * @param bool $useBackupApi True to use the backup API, false to use the primary API (default).
     *
     * @return void
     */
    public function setUseBackupApi(bool $useBackupApi)
    {
        $this->useBackupApi = $useBackupApi;
    }

    /**
     * @return bool
     */
    public function getUseBackupApi() : bool
    {
        return $this->useBackupApi;
    }

    /**
     * @param string $endpoint
     *
     * @return string
     */
    private function getApiUrl(string $endpoint) : string
    {
        return ($this->useBackupApi ? self::BACKUP_API : self::PRIMARY_API) . $endpoint;
    }

    /**
     * @param string      $text The UTF-8 message to send.
     * @param string      $to   The receiver's phone number with the country code.
     * @param string|null $from The sender name, or null to use the default API Sender Name.
     *
     * @return string A unique MessageID, which is specific to this message.
     *                This MessageID can be used later to check the Delivery status.
     *
     * @throws \RuntimeException
     */
    public function send(string $text, string $to, string $from = null) : string
    {
        $parameters = [
            'username' => $this->username,
            'password' => $this->password,
            'text'     => $text,
            'to'       => $to
        ];

        if ($from !== null) {
            $parameters['from'] = $from;
        }

        $url = $this->getApiUrl(self::SEND_SMS_ENDPOINT);

        try {
            $response = $this->client->post($url, [
                RequestOptions::QUERY => $parameters
            ]);
        } catch (RequestException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $body = (string) $response->getBody();

        if (preg_match('/^(OK|ERROR) (.+)$/', $body, $matches) !== 1) {
            throw new \RuntimeException('Invalid response received: ' . $body);
        }

        list (, $status, $value) = $matches;

        if ($status === 'ERROR') {
            throw new \RuntimeException('Error received from the API: ' . $value);
        }

        return $value;
    }
}
