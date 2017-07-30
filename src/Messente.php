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
    const PRIMARY_API_ENDPOINT = 'https://api2.messente.com/send_sms/';
    const BACKUP_API_ENDPOINT  = 'https://api3.messente.com/send_sms/';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

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

        try {
            $response = $this->client->post(self::PRIMARY_API_ENDPOINT, [
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
