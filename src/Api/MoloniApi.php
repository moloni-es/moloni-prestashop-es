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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Moloni\Entity\MoloniApp;
use Moloni\Enums\Domains;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniLoginException;

class MoloniApi
{
    /**
     * EntityManager
     *
     * @var EntityManager
     */
    private static $entityManager;

    /**
     * @var MoloniApp|null
     */
    private static $app;
    /**
     * @var Client|null
     */
    private static $client;

    public function __construct(EntityManager $entityManager, ?MoloniApp $app)
    {
        self::$app = $app;
        self::$entityManager = $entityManager;
    }

    /**
     * Get session
     *
     * @return MoloniApp|null
     */
    public static function getAppEntity(): ?MoloniApp
    {
        return self::$app;
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
            'apiClientId' => self::$app->getClientId(),
            'clientSecret' => self::$app->getClientSecret(),
            'code' => $code,
        ];

        try {
            $request = self::$client->post($url, ['body' => $params]);

            if ($request === null) {
                throw new MoloniLoginException('Request error');
            }

            $body = json_decode($request->getBody()->getContents(), false);

            if (!$body->accessToken || !$body->refreshToken) {
                throw new MoloniLoginException('Error fetching tokens', [], ['response' => $body]);
            }

            self::$app->setAccessToken($body->accessToken);
            self::$app->setRefreshToken($body->refreshToken);
            self::$app->setAccessTime(time());

            self::$entityManager->persist(self::$app);
            self::$entityManager->flush();
        } catch (BadResponseException $e) {
            throw new MoloniLoginException("The client credentials are invalid", [], ['response' => $e->getResponse()->json(), 'params' => $params]);
        } catch (ORMException $e) {
            throw new MoloniLoginException($e->getMessage());
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
            'apiClientId' => self::$app->getClientId(),
            'clientSecret' => self::$app->getClientSecret(),
            'refreshToken' => self::$app->getRefreshToken(),
        ];

        try {
            $request = self::$client->post($url, ['body' => $params]);

            if ($request === null) {
                throw new MoloniLoginException('Request error');
            }

            $body = json_decode($request->getBody()->getContents(), false);

            if (!$body->accessToken || !$body->refreshToken) {
                throw new MoloniLoginException('Error fetching tokens', [], ['response' => $body]);
            }

            self::$app->setAccessToken($body->accessToken);
            self::$app->setRefreshToken($body->refreshToken);
            self::$app->setAccessTime(time());

            self::$entityManager->persist(self::$app);
            self::$entityManager->flush();
        } catch (BadResponseException|MoloniLoginException|ORMException $e) {
            return false;
        }

        return true;
    }

    /**
     * Make authenticated request
     *
     * @param array|null $data
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public static function post(?array $data = []): array
    {
        if (!self::$client) {
            self::$client = new Client();
        }

        if (isset($data['variables']) && !isset($data['variables']['companyId'])) {
            $data['variables']['companyId'] = self::$app->getCompanyId();
        }

        try {
            $response = [];
            $request = self::$client->post(
                Domains::MOLONI_API,
                [
                    'headers' => [
                        'Authorization' => 'bearer ' . self::$app->getAccessToken(),
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($data),
                ]
            );

            if ($request !== null) {
                $json = $request->getBody()->getContents();

                $response = json_decode($json, true);
            }

            return $response;
        } catch (BadResponseException $e) {
            $response = $e->getResponse() ? $e->getResponse()->json() : [];

            throw new MoloniApiException('Request error', [], ['data' => $data, 'response' => $response]);
        }
    }

    /**
     * Verifies if company is selected
     *
     * @return bool
     */
    public static function hasValidCompany(): bool
    {
        if (empty(self::$app)) {
            return false;
        }

        return !empty(self::$app->getCompanyId());
    }

    /**
     * Verifies Moloni tokens validity
     *
     * @return bool
     */
    public static function hasValidAuthentication(): bool
    {
        if (empty(self::$app)) {
            return false;
        }

        if (empty(self::$app->getAccessToken()) || empty(self::$app->getRefreshToken())) {
            return false;
        }

        if (self::$app->isValidAccessToken()) {
            return true;
        }

        return self::$app->isValidRefreshToken() && self::refreshTokens();
    }
}