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
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Moloni\Entity\MoloniApp;
use Moloni\Enums\Domains;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniLoginException;
use Moloni\Mails\AuthenticationExpiredMail;
use Moloni\Tools\Settings;

if (!defined('_PS_VERSION_')) {
    exit;
}

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

    //          Gets          //

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
     * Get logged company id
     *
     * @return int
     */
    public static function getCompanyId(): int
    {
        return self::$app->getCompanyId() ?? 0;
    }

    //          Requests          //

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
            $request = self::$client->request('post', $url, ['json' => $params]);

            $json = $request->getBody()->getContents();

            $body = json_decode($json, false);

            if (!$body->accessToken || !$body->refreshToken) {
                throw new MoloniLoginException('Error fetching tokens', [], ['response' => $body]);
            }

            self::$app->setAccessToken($body->accessToken);
            self::$app->setRefreshToken($body->refreshToken);
            self::$app->setAccessTime(time());

            self::$entityManager->persist(self::$app);
            self::$entityManager->flush();
        } catch (BadResponseException $e) {
            $response = $e->getResponse()->getBody()->getContents();

            throw new MoloniLoginException("Invalid credentials", ['params' => $params, "response" => $response]);
        } catch (OptimisticLockException|ORMException $e) {
            throw new MoloniLoginException("Error saving credentials");
        } catch (GuzzleException $e) {
            throw new MoloniLoginException("Request error", ['message' => $e->getMessage()]);
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

        $hasError = false;
        $url = Domains::MOLONI_API . '/auth/grant';
        $params = [
            'grantType' => 'refresh_token',
            'apiClientId' => self::$app->getClientId(),
            'clientSecret' => self::$app->getClientSecret(),
            'refreshToken' => self::$app->getRefreshToken(),
        ];

        try {
            $request = self::$client->request('post', $url, ['form_params' => $params]);

            $body = json_decode($request->getBody()->getContents(), false);

            if (!$body->accessToken || !$body->refreshToken) {
                throw new MoloniLoginException('Error fetching tokens', [], ['response' => $body]);
            }

            self::$app->setAccessToken($body->accessToken);
            self::$app->setRefreshToken($body->refreshToken);
            self::$app->setAccessTime(time());

            self::$entityManager->persist(self::$app);
            self::$entityManager->flush();
        } catch (BadResponseException|ORMException|MoloniLoginException|GuzzleException $e) {
            $hasError = true;
        }

        if ($hasError) {
            if (!empty(Settings::get('alertEmail'))) {
                (new AuthenticationExpiredMail(Settings::get('alertEmail'), []))->handle();
            }

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

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (self::$app) {
            if (isset($data['variables']) && !isset($data['variables']['companyId'])) {
                $data['variables']['companyId'] = self::$app->getCompanyId();
            }

            $headers['Authorization'] = 'bearer ' . self::$app->getAccessToken();
        }

        try {
            $request = self::$client->request('post', Domains::MOLONI_API,
                [
                    'headers' => $headers,
                    'json' => $data,
                ]
            );

            $json = $request->getBody()->getContents();

            return json_decode($json, true) ?? [];
        } catch (BadResponseException $e) {
            $response = $e->getResponse()->getBody()->getContents();

            throw new MoloniApiException("Request error", ['data' => $data, "response" => $response]);
        } catch (GuzzleException $e) {
            throw new MoloniApiException($e->getMessage(), ['data' => $data]);
        }
    }

    /**
     * Make authenticated request with file
     *
     * @param array|null $operations
     * @param string|null $map
     * @param array|null $files
     * @return array
     *
     * @throws MoloniApiException
     */
    public static function postWithFile(?array $operations = [], ?string $map = '', ?array $files = []): array
    {
        if (!self::$client) {
            self::$client = new Client();
        }

        if (isset($operations['variables']) && !isset($operations['variables']['companyId'])) {
            $operations['variables']['companyId'] = self::$app->getCompanyId();
        }

        try {
            $data = [];
            $headers = ['Authorization' => 'bearer ' . self::$app->getAccessToken()];

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

            $request = self::$client->request('post', Domains::MOLONI_API, [
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

    //          Verifications          //

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
