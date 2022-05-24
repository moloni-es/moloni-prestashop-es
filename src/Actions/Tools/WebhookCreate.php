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

namespace Moloni\Actions\Tools;

use Configuration;
use Db;
use Moloni\Enums\Boolean;
use WebserviceKey;
use PrestaShopException;
use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;

class WebhookCreate
{
    private $key = '';
    private $url = '';

    /**
     * Creates moloni Webhook
     *
     * @throws MoloniException
     */
    public function handle($model, $operation): void
    {
        if ((int)Configuration::get('PS_WEBSERVICE') === Boolean::NO) {
            $this->enableWebServices();
        }

        if (empty($this->key)) {
            try {
                $this->fillKey();
            } catch (PrestaShopException $e) {
                throw new MoloniException('Error creating webservice key');
            }
        }

        $props = [
            'model' => $model,
            'url' => $this->url,
            'operation' => $operation
        ];

        try {
            MoloniApiClient::hooks()->mutationHookCreate(['data' => $props]);
        } catch (MoloniApiException $e) {
            throw new MoloniException('Error creating {0} hook', ['{0}' => $operation], [$e->getData()]);
        }
    }

    /**
     * Enable Webservices in Prestashop
     */
    private function enableWebServices(): void
    {
        Configuration::updateValue('PS_WEBSERVICE', 1);
    }

    private function setUrl(): void
    {
        $baseUrlSecure = defined('_PS_BASE_URL_SSL_') ? _PS_BASE_URL_SSL_ : '';
        $baseUrl = defined('__PS_BASE_URI__') ? __PS_BASE_URI__ : '';

        //the authorization key needs to be after http(s):// and before the actual domain
        //(example: http://{key}@domain.com)
        $strlen = strlen('://');
        $strpos = strpos($baseUrlSecure, '://');

        $url = substr_replace($baseUrlSecure, '://' . $this->key . '@', $strpos, $strlen);
        $url .= $baseUrl . 'api/moloniresource/';

        $this->url = $url;
    }

    /**
     * @throws PrestaShopException
     */
    private function fillKey(): void
    {
        $key = $this->fetchWebserviceKey();

        if (empty($key)) {
            $key = $this->createWebserviceKey();
        }

        $this->key = $key;

        $this->setUrl();
    }

    private function fetchWebserviceKey(): string
    {
        $key = '';

        $dataBase = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'webservice_account WHERE description = "Moloni WebHooks key"';
        $query = $dataBase->getRow($sql);


        if ($query !== false) {
            $key = $query['key'];
        }

        return $key;
    }

    /**
     * @throws PrestaShopException
     */
    private function createWebserviceKey(): string
    {
        $randKey = substr(str_shuffle(MD5(microtime())), 0, 32);

        $apiAccess = new WebserviceKey();
        $apiAccess->key = $randKey;
        $apiAccess->description = 'Moloni WebHooks key';
        $apiAccess->save();

        $permissions = [
            'moloniproducts' => ['POST' => 1],
        ];

        WebserviceKey::setPermissionForAccount($apiAccess->id, $permissions);

        return $apiAccess->key;
    }
}
