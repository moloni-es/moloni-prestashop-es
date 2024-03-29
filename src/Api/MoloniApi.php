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
use Moloni\Entity\MoloniApp;
use Moloni\Enums\Domains;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniLoginException;
use Moloni\Guzzle\GuzzleWrapper;
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
        if (empty(self::$app)) {
            return 0;
        }

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

        $url = Domains::MOLONI_API . '/auth/grant';
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $params = [
            'grantType' => 'authorization_code',
            'apiClientId' => self::$app->getClientId(),
            'clientSecret' => self::$app->getClientSecret(),
            'code' => $code,
        ];

        try {
            $body = GuzzleWrapper::post($url, $headers, $params);
        } catch (MoloniApiException $e) {
            throw new MoloniLoginException("The client credentials are invalid", [], $e->getData());
        }

        if (empty($body)) {
            throw new MoloniLoginException('Request error');
        }
        if (!$body['accessToken'] || !$body['refreshToken']) {
            throw new MoloniLoginException('Error fetching tokens', [], ['response' => $body]);
        }

        try {
            self::$app->setAccessToken($body['accessToken']);
            self::$app->setRefreshToken($body['refreshToken']);
            self::$app->setAccessTime(time());

            self::$entityManager->persist(self::$app);
            self::$entityManager->flush();
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
        $url = Domains::MOLONI_API . '/auth/grant';
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $params = [
            'grantType' => 'refresh_token',
            'apiClientId' => self::$app->getClientId(),
            'clientSecret' => self::$app->getClientSecret(),
            'refreshToken' => self::$app->getRefreshToken(),
        ];

        try {
            $body = GuzzleWrapper::post($url, $headers, $params);

            if (empty($body)) {
                throw new MoloniLoginException('Request error');
            }

            if (!$body['accessToken'] || !$body['refreshToken']) {
                throw new MoloniLoginException('Error fetching tokens', [], ['response' => $body]);
            }
        } catch (MoloniApiException $e) {
            if (!empty(Settings::get('alertEmail'))) {
                (new AuthenticationExpiredMail(Settings::get('alertEmail'), ['message' => $e->getMessage()]))->handle();
            }

            return false;
        } catch (MoloniLoginException $e) {
            if (!empty(Settings::get('alertEmail'))) {
                (new AuthenticationExpiredMail(Settings::get('alertEmail'), $e->getData()))->handle();
            }

            return false;
        }

        try {
            self::$app->setAccessToken($body['accessToken']);
            self::$app->setRefreshToken($body['refreshToken']);
            self::$app->setAccessTime(time());

            self::$entityManager->persist(self::$app);
            self::$entityManager->flush();
        } catch (ORMException $e) {
            if (!empty(Settings::get('alertEmail'))) {
                (new AuthenticationExpiredMail(Settings::get('alertEmail'), ['message' => $e->getMessage()]))->handle();
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
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (self::$app) {
            if (!empty(self::$app->getCompanyId()) && isset($data['variables']) && !isset($data['variables']['companyId'])) {
                $data['variables']['companyId'] = self::$app->getCompanyId();
            }

            if (!empty(self::$app->getAccessToken())) {
                $headers['Authorization'] = 'bearer ' . self::$app->getAccessToken();
            }
        }

        if (empty($data['variables'])) {
            unset($data['variables']);
        }

        $body = GuzzleWrapper::post(Domains::MOLONI_API, $headers, $data);

        return empty($body) ? [] : $body;
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
        if (isset($operations['variables']) && !isset($operations['variables']['companyId'])) {
            $operations['variables']['companyId'] = self::$app->getCompanyId();
        }

        $response = GuzzleWrapper::postWithFile($operations, $map, $files, self::$app->getAccessToken());

        return $response ?? [];
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
