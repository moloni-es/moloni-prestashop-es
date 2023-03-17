<?php

namespace Moloni\Guzzle;

use Moloni\Exceptions\MoloniApiException;
use Moloni\Helpers\Version;

class GuzzleWrapper
{
    /**
     * @var GuzzleInterface|null
     */
    private static $guzzleClient;

    private static function startGuzzleClient()
    {
        if (Version::isPrestashopNewVersion()) {
            self::$guzzleClient = new Guzzle6();
        } else {
            self::$guzzleClient = new Guzzle5();
        }
    }

    /**
     * Do post request
     *
     * @param string $url Request url
     * @param array|null $headers Request headers
     * @param array|null $body Request body
     *
     * @return mixed|null
     *
     * @throws MoloniApiException
     */
    public static function post(string $url, ?array $headers = [], ?array $body = [])
    {
        if (!self::$guzzleClient) {
            self::startGuzzleClient();
        }

        return self::$guzzleClient->post($url, $headers, $body);
    }

    /**
     * Make authenticated post request with file
     *
     * @param array|null $operations
     * @param string|null $map
     * @param array|null $files
     * @param string|null $accessToken
     *
     * @return mixed|null
     *
     * @throws MoloniApiException
     */
    public static function postWithFile(?array $operations = [], ?string $map = '', ?array $files = [], ?string $accessToken = '')
    {
        if (!self::$guzzleClient) {
            self::startGuzzleClient();
        }

        return self::$guzzleClient->postWithFile($operations, $map, $files, $accessToken);
    }
}