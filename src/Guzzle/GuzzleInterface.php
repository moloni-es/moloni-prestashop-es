<?php

namespace Moloni\Guzzle;

use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface GuzzleInterface
{
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
    public function post(string $url, ?array $headers = [], ?array $body = []);

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
    public function postWithFile(?array $operations = [], ?string $map = '', ?array $files = [], ?string $accessToken = '');
}