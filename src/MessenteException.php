<?php

namespace Messente;

/**
 * Thrown when an error code is received from the API.
 */
class MessenteException extends \Exception
{
    /**
     * Access is restricted, wrong credentials. Check the username and password values.
     */
    const INVALID_CREDENTIALS = 'ERROR 101';

    /**
     * Parameters are wrong or missing. Check that all the required parameters are present.
     */
    const INVALID_PARAMETERS = 'ERROR 102';

    /**
     * Invalid IP address. The IP address you made the request from, is not in the whitelist.
     */
    const INVALID_IP_ADDRESS = 'ERROR 103';

    /**
     * Could not find message with this message ID.
     */
    const UNKNOWN_MESSAGE_ID = 'ERROR 107';

    /**
     * Sender parameter is invalid. You have not activated this sender name.
     */
    const INVALID_SENDER_NAME = 'ERROR 111';

    /**
     * No delivery report yet, try again in 5 seconds.
     */
    const NO_DELIVERY_REPORT_YET = 'FAILED 102';

    /**
     * Server failure, try again after a few seconds or try the backup server.
     */
    const SERVER_FAILURE = 'FAILED 209';

    const ERROR_MESSAGES = [
        self::INVALID_CREDENTIALS    => 'Access is restricted, wrong credentials. Check the username and password values',
        self::INVALID_PARAMETERS     => 'Parameters are wrong or missing. Check that all the required parameters are present.',
        self::INVALID_IP_ADDRESS     => 'Invalid IP address. The IP address you made the request from, is not in the whitelist.',
        self::UNKNOWN_MESSAGE_ID     => 'Could not find message with this message ID.',
        self::INVALID_SENDER_NAME    => 'Sender parameter is invalid. You have not activated this sender name.',
        self::NO_DELIVERY_REPORT_YET => 'No delivery report yet, try again in 5 seconds.',
        self::SERVER_FAILURE         => 'Server failure, try again after a few seconds or try the backup server.'
    ];

    /**
     * @var string|null
     */
    private $errorCode;

    /**
     * @return string|null
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param string $code The error code.
     *
     * @return MessenteException
     */
    public static function forErrorCode(string $code) : MessenteException
    {
        if (isset(self::ERROR_MESSAGES[$code])) {
            $message = self::ERROR_MESSAGES[$code];
        } else {
            $message = 'Unknown Messente API error';
        }

        $exception = new self($message);
        $exception->errorCode = $code;

        return $exception;
    }

    /**
     * @param string $response
     *
     * @return MessenteException
     */
    public static function invalidResponse(string $response) : MessenteException
    {
        return new self('Invalid response received from Messente API: ' . $response);
    }
}
