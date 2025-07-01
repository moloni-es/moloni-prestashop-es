<?php

/**
 * 2025 - Moloni.com
 *
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Moloni
 * @copyright Moloni
 * @license   https://creativecommons.org/licenses/by-nd/4.0/
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

namespace Moloni\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Post\PostFile;
use Moloni\Configurations;
use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Guzzle5 extends GuzzleAbstract implements GuzzleInterface
{
    public function __construct(Configurations $configurations)
    {
        parent::__construct($configurations);

        $headers = ['User-Agent' => $this->userAgent];

        $this->client = new Client();

        $defaultHeaders = $this->client->getDefaultOption('headers');

        if (is_array($defaultHeaders)) {
            $headers = array_merge($defaultHeaders, $headers);
        }

        $this->client->setDefaultOption('headers', $headers);
    }

    public function post(string $url, ?array $headers = [], ?array $body = [])
    {
        try {
            $request = $this->client->post($url, ['headers' => $headers, 'body' => json_encode($body)]);
        } catch (BadResponseException $e) {
            try {
                $response = $e->getResponse() ? $e->getResponse()->json() : [];
            } catch (ParseException $e) {
                $response = [];
            }

            throw new MoloniApiException('Request error', [], ['data' => $body, 'response' => $response]);
        }

        if ($request === null) {
            return null;
        }

        return json_decode($request->getBody()->getContents(), true);
    }

    public function postWithFile(?array $operations = [], ?string $map = '', ?array $files = [], ?string $accessToken = '')
    {
        try {
            $request = $this->client->createRequest('POST', $this->apiUrl);
            $postBody = $request->getBody();

            if (!empty($operations)) {
                $postBody->setField('operations', json_encode($operations));
            }

            if (!empty($map)) {
                $postBody->setField('map', $map);
            }

            if (!empty($files)) {
                foreach ($files as $idx => $file) {
                    if (!file_exists($file)) {
                        continue;
                    }

                    $rawImage = fopen($file, 'rb');

                    if (!empty($rawImage)) {
                        $postBody->addFile(new PostFile((string) $idx, $rawImage));
                    }
                }
            }

            $request->addHeader('Authorization', 'bearer ' . $accessToken);

            $request = $this->client->send($request);
        } catch (BadResponseException $e) {
            try {
                $response = $e->getResponse() ? $e->getResponse()->json() : [];
            } catch (ParseException $e) {
                $response = [];
            }

            throw new MoloniApiException('Request error', [], ['data' => $operations, 'response' => $response]);
        }

        if ($request === null) {
            return null;
        }

        return json_decode($request->getBody()->getContents(), true);
    }
}
