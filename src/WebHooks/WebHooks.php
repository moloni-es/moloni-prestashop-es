<?php

/** @noinspection ALL */

namespace Moloni\ES\WebHooks;

use Configuration;
use Moloni\ES\Controllers\Api\Companies;
use Moloni\ES\Controllers\Api\Hooks;
use Moloni\ES\Controllers\Models\Company;
use WebserviceKey;

class WebHooks
{
    /**
     * List of hooks
     * ['presta resource' => 'moloni model']
     *
     * @var string[]
     */
    public static $models = [
        'moloniproducts' => 'Product',
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
            'moloniproducts' => ['POST' => 1],
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

        $dataBase->delete(
            'webservice_account',
            'description = "Moloni WebHooks key"'
        );
    }

    /**
     * Creates hash key to authenticate in WebService Request (based on company slug)
     *
     * @return string
     */
    public static function createComplexValue()
    {
        $variables = ['companyId' => (int) Company::get('company_id')];

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
        self::deleteHooks(); //prevent multiple hooks from doing the same
        $key = self::createCredentials();

        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'data' => [],
        ];

        foreach (self::$models as $route => $model) {
            //the authorization key needs to be after http(s):// and before the actual domain
            //(example: http://{key}@domain.com)
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
            'companyId' => (int) Company::get('company_id'),
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
