<?php

namespace Moloni\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Post\PostFile;
use Moloni\Enums\Domains;
use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Guzzle5 extends GuzzleAbstract implements GuzzleInterface
{
    public function __construct()
    {
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

        return json_decode($request->getBody()->getContents(), false);
    }

    public function postWithFile(?array $operations = [], ?string $map = '', ?array $files = [], ?string $accessToken = '')
    {
        try {
            $request = $this->client->createRequest('POST', Domains::MOLONI_API);
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
                        $postBody->addFile(new PostFile((string)$idx, $rawImage));
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