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
use Moloni\Enums\Domains;
use Moloni\Exceptions\MoloniApiException;

class MoloniApi
{
    /**
     * @var array|null
     */
    private static $appSession;
    /**
     * @var Client|null
     */
    private static $client;

    public static function loadSession(array $appSession): void
    {
        self::$appSession = $appSession;
    }

    public static function getSession(): array
    {
        return self::$appSession;
    }

    public static function login(): void
    {
    }

    public static function refreshTokens(): void
    {
    }

    public static function isValid(): bool
    {
        return true;
    }

    /**
     * @throws MoloniApiException
     */
    public static function post(array $data = [])
    {
        if (!self::$client) {
            self::$client = new Client();
        }

        try {
            return self::$client->post(
                Domains::MOLONI_API, [
                    'headers' => [
                        'Authorization' => 'bearer ' . self::$appSession['access_token'],
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($data),
                ]
            );
        } catch (BadResponseException $e) {
            $response = $e->getResponse() ? $e->getResponse()->json() : [];

            throw new MoloniApiException('Request error', [], ['data' => $data, 'response' => $response]);
        }
    }
}
