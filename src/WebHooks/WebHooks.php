<?php

/** @noinspection ALL */

namespace Moloni\ES\WebHooks;

use Configuration;
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
            'moloniproducts' => ['GET' => 0, 'POST' => 1, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        ];

        WebserviceKey::setPermissionForAccount($apiAccess->id, $permissions);

        return $apiAccess->key;
    }

    /**
     * Creates hash key to authenticate in WebService Request
     *
     * @return string
     */
    public static function createComplexValue()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * Creates hooks in Moloni
     *
     * @throws \PrestaShopException
     */
    public static function createHooks()
    {
        self::enableWebServices();
        $key = self::createCredentials();

        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'data' => [],
        ];

        foreach (self::$models as $route => $model) {
            //the authorization key needs to be after http(s):// and before the actual domain
            //(example: http://{key}@domain.com)
            $url = substr_replace(_PS_BASE_URL_, '://' . $key . '@', strpos(_PS_BASE_URL_, '://'), strlen('://'));

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
        $ids = [];

        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'data' => [
                'search' => [
                    'field' => 'url',
                    'value' => '/api/moloni',
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
