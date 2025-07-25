<?php

namespace ConvertApi;
use ConvertApi\Task;

/**
 * Class ConvertApi
 *
 * @package ConvertApi
 */
class ConvertApi
{
    // ConvertAPI client version.
    const VERSION = '3.0.0';

    // @var string The Convert API credentials. You can get your credentials at https://www.convertapi.com/a
    public static $apiCredentials;

    // @var string The base URL for the Convert API
    public static $apiBase = 'https://v2.convertapi.com/';

    // @var string HTTP connection timeout.
    public static $connectTimeout = 5;

    // @var string HTTP read timeout.
    public static $readTimeout = 1800;

    // @var string Conversion timeout.
    public static $conversionTimeout;

    // @var string Conversion timeout delta.
    public static $conversionTimeoutDelta = 10;

    // @var string File upload timeout.
    public static $uploadTimeout = 1800;

    // @var string File download timeout.
    public static $downloadTimeout = 1800;

    // @var static \ConvertApi\Client
    private static $_client;

    /**
     * @return string The API credentials used for requests.
     */
    public static function getApiCredentials()
    {
        return self::$apiCredentials;
    }

    /**
     * Sets API secret or token used for requests.
     *
     * @param string $apiCredentials
     */
    public static function setApiCredentials($apiCredentials)
    {
        self::$apiCredentials = $apiCredentials;
    }

    /**
     * @return string The API base used for requests.
     */
    public static function getApiBase()
    {
        return self::$apiBase;
    }

    /**
     * Sets API base used for requests.
     *
     * @param string $apiBase
     */
    public static function setApiBase($apiBase)
    {
        self::$apiBase = $apiBase;
    }

    /**
     * Perform conversion
     *
     * @param string $toFormat Convert to format
     * @param array $params Conversion parameters
     * @param string $fromFormat Convert from format
     * @param int $conversionTimeout Conversion timeout
     *
     * @return \ConvertApi\Result Conversion result
     */
    public static function convert($toFormat, $params, $fromFormat = null, $conversionTimeout = null)
    {
        $task = new Task($fromFormat, $toFormat, $params, $conversionTimeout);

        return $task->run();
    }

    /**
     * @return array User information
     */
    public static function getUser()
    {
        return self::client()->get('user');
    }

    /**
     * @return \ConvertApi\Client API client
     */
    public static function client()
    {
        if (!isset(self::$_client))
            self::$_client = new Client;

        return self::$_client;
    }
}