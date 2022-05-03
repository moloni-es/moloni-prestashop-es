<?php
/**
 * 2022 - Moloni.com
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

namespace Moloni\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Moloni\Entity\MoloniApp;
use Moloni\Enums\Domains;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniLoginException;

class MoloniApi
{
    /**
     * @var MoloniApp
     */
    private static $appSession;
    /**
     * @var Client|null
     */
    private static $client;

    public static $test = 'null';

    public function __construct($test)
    {
        self::$test = $test;
    }

    /**
     * Load session
     *
     * @param MoloniApp $appSession
     *
     * @return void
     */
    public static function loadSession(MoloniApp $appSession): void
    {
        self::$appSession = $appSession;
    }

    /**
     * Get session
     *
     * @return MoloniApp
     */
    public static function getSession(): MoloniApp
    {
        return self::$appSession;
    }

    /**
     * Login action
     *
     * @param string $code
     *
     * @return bool
     *
     * @throws MoloniLoginException
     */
    public static function login(string $code): bool
    {
        if (empty($code)) {
            throw new MoloniLoginException('Code missing');
        }

        if (!self::$client) {
            self::$client = new Client();
        }

        $url = Domains::MOLONI_API . '/auth/grant';
        $params = [
            'grantType' => 'authorization_code',
            'apiClientId' => self::$appSession->getClientId(),
            'clientSecret' => self::$appSession->getClientSecret(),
            'code' => $code,
        ];

        try {
            $request = self::$client->post($url, ['body' => $params]);

            if (empty($request)) {
                throw new MoloniLoginException('Request error');
            }

            $body = json_decode($request->json(), false);

            if (empty($body['accessToken']) || empty($body['refreshToken'])) {
                throw new MoloniLoginException('Error fetching tokens', [], ['response' => $body]);
            }

            self::$appSession->setAccessToken($body['accessToken']);
            self::$appSession->setRefreshToken($body['refreshToken']);
            self::$appSession->setLoginDate(time());
            self::$appSession->setAccessTime(time());

            // todo: how to save here?
        } catch (BadResponseException $e) {
            throw new MoloniLoginException($e->getMessage(), [], ['response' => $e->getResponse(), 'request' => $e->getRequest()]);
        }

        return true;
    }

    /**
     * Refresh tokens action
     *
     * @return bool
     */
    public static function refreshTokens(): bool
    {
        if (!self::$client) {
            self::$client = new Client();
        }

        $url = Domains::MOLONI_API . '/auth/grant';
        $params = [
            'grantType' => 'refresh_token',
            'apiClientId' => self::$appSession->getClientId(),
            'clientSecret' => self::$appSession->getClientSecret(),
            'refreshToken' => self::$appSession->getRefreshToken(),
        ];

        try {
            $request = self::$client->post($url, ['body' => $params]);

            if ($request === null) {
                throw new MoloniLoginException('Request error');
            }

            $body = json_decode($request->json(), false);

            if (empty($body['accessToken']) || empty($body['refreshToken'])) {
                throw new MoloniLoginException('Error fetching tokens', [], ['response' => $body]);
            }

            self::$appSession->setAccessToken($body['accessToken']);
            self::$appSession->setRefreshToken($body['refreshToken']);
            self::$appSession->setAccessTime(time());

            // todo: how to save here?
        } catch (BadResponseException|MoloniLoginException $e) {
            return false;
        }

        return true;
    }

    /**
     * Verifies Moloni tokens validity
     *
     * @return bool
     */
    public static function isValid(): bool
    {
        return self::$appSession->isValidAccessToken() ||
            (self::$appSession->isValidRefreshToken() && self::refreshTokens());
    }

    /**
     * Make authenticated request
     *
     * @throws MoloniApiException
     */
    public static function post(array $data = []): array
    {
        if (!self::$client) {
            self::$client = new Client();
        }

        try {
            $response = [];
            $request = self::$client->post(
                Domains::MOLONI_API, [
                    'headers' => [
                        'Authorization' => 'bearer ' . self::$appSession['access_token'],
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($data),
                ]
            );

            if ($request !== null && $request->json()) {
                $response = json_decode($request->json(), false);
            }

            return $response;
        } catch (BadResponseException $e) {
            $response = $e->getResponse() ? $e->getResponse()->json() : [];

            throw new MoloniApiException('Request error', [], ['data' => $data, 'response' => $response]);
        }
    }
}
