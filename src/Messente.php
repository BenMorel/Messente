<?php

declare(strict_types=1);

namespace Messente;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

use Psr\Http\Message\ResponseInterface;

/**
 * Sends SMS messages using the Messente API.
 */
class Messente
{
    const PRIMARY_API = 'https://api2.messente.com';
    const BACKUP_API  = 'https://api3.messente.com';

    const SEND_SMS_ENDPOINT         = '/send_sms/';
    const GET_DLR_RESPONSE_ENDPOINT = '/get_dlr_response/';

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
    public function setUseBackupApi(bool $useBackupApi) : void
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
     * @throws RequestException  If the HTTP request fails.
     * @throws MessenteException If an error is received from the API.
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

        $response = $this->client->post($url, [
            RequestOptions::QUERY => $parameters
        ]);

        return $this->getResponse($response);
    }

    /**
     * Queries the status of a message.
     *
     * @param string $messageId The message ID returned by `send()`.
     *
     * @return string The message status: 'SENT', 'FAILED' or 'DELIVERED'.
     *
     * @throws RequestException  If the HTTP request fails.
     * @throws MessenteException If an error is received from the API.
     */
    public function getStatus(string $messageId) : string
    {
        $parameters = [
            'username'      => $this->username,
            'password'      => $this->password,
            'sms_unique_id' => $messageId
        ];

        $url = $this->getApiUrl(self::GET_DLR_RESPONSE_ENDPOINT);

        $response = $this->client->get($url, [
            RequestOptions::QUERY => $parameters
        ]);

        return $this->getResponse($response);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return string
     *
     * @throws MessenteException
     */
    private function getResponse(ResponseInterface $response) : string
    {
        $body = (string) $response->getBody();

        if (preg_match('/^(OK|ERROR|FAILURE) (.+)$/', $body, $matches) !== 1) {
            throw MessenteException::invalidResponse($body);
        }

        list ($code, $status, $value) = $matches;

        if ($status === 'OK') {
            return $value;
        }

        throw MessenteException::forErrorCode($code);
    }
}
