<?php

namespace Moloni\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Moloni\Enums\Domains;
use Moloni\Exceptions\MoloniApiException;

class Guzzle6 extends GuzzleAbstract implements GuzzleInterface
{
    public function __construct()
    {
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
                    'contents' => json_encode($operations)
                ];
            }

            if (!empty($map)) {
                $data[] = [
                    'name' => 'map',
                    'contents' => $map
                ];
            }

            if (!empty($files)) {
                foreach ($files as $idx => $file) {
                    $data[] = [
                        'name' => (string)$idx,
                        'contents' => fopen($file, 'rb'),
                    ];
                }
            }

            $request = $this->client->request('post', Domains::MOLONI_API, [
                'headers' => $headers,
                'multipart' => $data
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