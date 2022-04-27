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
 */

namespace Moloni\Webservice;

use Configuration;
use Moloni\Api\Endpoints\Hooks;
use Moloni\Api\Endpoints\Companies;
use Moloni\Helpers\Moloni;
use WebserviceKey;

class Webservice
{
    /**
     * List of hooks
     * ['presta resource' => 'moloni model']
     *
     * @var string[]
     */
    private static $models = [
        'moloniresource' => 'Product',
    ];

    /**
     * Enable Webservices in Prestashop
     */
    public static function enableWebServices()
    {
        Configuration::updateValue('PS_WEBSERVICE', 1);
    }

    /**
     * Disable Webservices in Prestashop
     *
     * @return string
     *
     * @throws \PrestaShopException
     */
    public static function createCredentials()
    {
        $apiAccess = new WebserviceKey();
        $apiAccess->key = self::createComplexValue();
        $apiAccess->description = 'Moloni WebHooks key';
        $apiAccess->save();

        $permissions = [
            'moloniresource' => ['POST' => 1],
        ];

        WebserviceKey::setPermissionForAccount($apiAccess->id, $permissions);

        return $apiAccess->key;
    }

    /**
     * Delete WebService key from prestashop
     */
    public static function deleteCredentials()
    {
        $dataBase = \Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'webservice_account WHERE description = "Moloni WebHooks key"';
        $query = $dataBase->getRow($sql);

        if ($query !== false) {
            $webserviceObj = new \WebserviceKeyCore($query['id_webservice_account']);
            $webserviceObj->delete();
        }
    }

    /**
     * Creates hash key to authenticate in WebService Request (based on company slug)
     *
     * @return string
     */
    public static function createComplexValue()
    {
        $variables = ['companyId' => (int) Moloni::get('company_id')];

        $result = Companies::queryCompany($variables);
        $result = $result['data']['company']['data'];

        return md5($result['slug']);
    }

    /**
     * Creates hooks in Moloni
     *
     * @throws \PrestaShopException
     */
    public static function createHooks()
    {
        self::enableWebServices();
        self::deleteHooks(); // prevent multiple hooks from doing the same
        $key = self::createCredentials();

        $variables = [
            'companyId' => (int) Moloni::get('company_id'),
            'data' => [],
        ];

        foreach (self::$models as $route => $model) {
            // the authorization key needs to be after http(s):// and before the actual domain
            // (example: http://{key}@domain.com)
            $baseUrl = _PS_BASE_URL_SSL_;
            $url = substr_replace($baseUrl, '://' . $key . '@', strpos($baseUrl, '://'), strlen('://'));

            $variables['data'] = [
                'model' => $model,
                'url' => $url . __PS_BASE_URI__ . 'api/' . $route . '/',
            ];

            Hooks::mutationHookCreate($variables);
        }
    }

    /**
     * Deletes hooks in Moloni
     */
    public static function deleteHooks()
    {
        self::deleteCredentials();

        $ids = [];

        $variables = [
            'companyId' => (int) Moloni::get('company_id'),
            'data' => [
                'search' => [
                    'field' => 'url',
                    'value' => self::createComplexValue(),
                ],
            ],
        ];

        $query = Hooks::queryHooks($variables);

        foreach ($query as $hook) {
            $ids[] = $hook['hookId'];
        }

        unset($variables['data']);
        $variables['hookId'] = $ids;

        Hooks::mutationHookDelete($variables);
    }
}
