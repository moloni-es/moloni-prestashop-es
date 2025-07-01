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
use GuzzleHttp\Exception\GuzzleException;
use Moloni\Configurations;
use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Guzzle6 extends GuzzleAbstract implements GuzzleInterface
{
    public function __construct(Configurations $configurations)
    {
        parent::__construct($configurations);

        $this->client = new Client(['headers' => ['User-Agent' => $this->userAgent]]);
    }

    public function post(string $url, ?array $headers = [], ?array $body = [])
    {
        try {
            $request = $this->client->request('post', $url, ['headers' => $headers, 'json' => $body]);
        } catch (BadResponseException $e) {
            $response = $e->getResponse()->getBody()->getContents();

            throw new MoloniApiException('Request error', [], ['data' => $body, 'response' => $response]);
        } catch (GuzzleException $e) {
            throw new MoloniApiException($e->getMessage(), ['data' => $body]);
        }

        return json_decode($request->getBody()->getContents(), true);
    }

    public function postWithFile(?array $operations = [], ?string $map = '', ?array $files = [], ?string $accessToken = '')
    {
        try {
            $data = [];
            $headers = ['Authorization' => 'bearer ' . $accessToken];

            if (!empty($operations)) {
                $data[] = [
                    'name' => 'operations',
                    'contents' => json_encode($operations),
                ];
            }

            if (!empty($map)) {
                $data[] = [
                    'name' => 'map',
                    'contents' => $map,
                ];
            }

            if (!empty($files)) {
                foreach ($files as $idx => $file) {
                    $data[] = [
                        'name' => (string) $idx,
                        'contents' => fopen($file, 'rb'),
                    ];
                }
            }

            $request = $this->client->request('post', $this->apiUrl, [
                'headers' => $headers,
                'multipart' => $data,
            ]);

            $json = $request->getBody()->getContents();

            return json_decode($json, true);
        } catch (BadResponseException $e) {
            $response = $e->getResponse()->getBody()->getContents();

            throw new MoloniApiException('Request error', [], ['data' => $operations, 'response' => $response]);
        } catch (GuzzleException $e) {
            throw new MoloniApiException($e->getMessage(), ['data' => $operations]);
        }
    }
}
