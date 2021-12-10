<?php

/**
 * 2020 - Moloni.com
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

use Moloni\ES\Controllers\Models\Settings;
use Moloni\ES\Hooks\PaymentConfirmation;
use Moloni\ES\Hooks\ProductSave;
use Moloni\ES\Install\Installer;

/** @noinspection AutoloadingIssuesInspection */

// @codingStandardsIgnoreLine
class MoloniPrestashopEs extends Module
{
    /**
     * Configuration data
     *
     * @var string[][]
     */
    private $configuration = [];

    /**
     * Hooks list
     *
     * @var string[]
     */
    private $hooks = [];

    /**
     * MoloniPrestashopEs constructor.
     */
    public function __construct()
    {
        $this->name = 'moloniprestashopes';
        $this->tab = 'administration';
        $this->need_instance = 1;
        $this->version = '1.1.10';
        $this->ps_versions_compliancy = ['min' => '1.7.5', 'max' => _PS_VERSION_];
        $this->author = 'Moloni';

        parent::__construct();

        $this->displayName = $this->trans('Moloni ES', [], 'Modules.Moloniprestashopes.Moloniprestashopes');
        $this->description = $this->trans(
            'Transform all your orders in verified documents 
        without any effort and focus on selling!',
            [],
            'Modules.Moloniprestashopes.Moloniprestashopes'
        );

        $this->autoload();

        //add the needed hooks to the configuration
        $this->hooks = [
            'actionAdminControllerSetMedia',
            'actionPaymentConfirmation',
            'actionProductAdd',
            'actionProductUpdate',
            'addWebserviceResources',
        ];
    }

    /**
     * Enable new translations module
     *
     * @return bool
     */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * Install plugin
     *
     * @return bool
     */
    public function install()
    {
        try {
            if (!(new Installer($this, $this->configuration))->install()) {
                return false;
            }
        } catch (Exception $exception) {
            $this->_errors[] = $exception->getMessage();

            return false;
        }

        if (!parent::install()) {
            return false;
        }

        foreach ($this->hooks as $hookName) {
            if (!$this->registerHook($hookName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstall plugin
     *
     * @return bool
     */
    public function uninstall()
    {
        try {
            if (!(new Installer($this, $this->configuration))->uninstall()) {
                return false;
            }
        } catch (Exception $exception) {
            $this->_errors[] = $exception->getMessage();

            return false;
        }

        if (!parent::uninstall()) {
            return false;
        }

        foreach ($this->hooks as $hookName) {
            if (!$this->unregisterHook(Hook::getIdByName($hookName))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Enable plugin
     *
     * @param bool $force_all
     *
     * @return bool
     */
    public function enable($force_all = false)
    {
        try {
            if (!(new Installer($this, $this->configuration))->enable()) {
                return false;
            }
        } catch (Exception $exception) {
            $this->_errors[] = $exception->getMessage();

            return false;
        }

        if (!parent::enable($force_all)) {
            return false;
        }

        foreach ($this->hooks as $hookName) {
            if (!$this->registerHook($hookName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Disable plugin
     *
     * @param bool $force_all
     *
     * @return bool
     */
    public function disable($force_all = false)
    {
        try {
            if (!(new Installer($this, $this->configuration))->disable()) {
                return false;
            }
        } catch (Exception $exception) {
            $this->_errors[] = $exception->getMessage();

            return false;
        }

        if (!parent::disable($force_all)) {
            return false;
        }

        foreach ($this->hooks as $hookName) {
            if (!$this->unregisterHook(Hook::getIdByName($hookName))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add endpoints to Prestashop Webservices
     *
     * @return array[]
     */
    public function hookAddWebserviceResources()
    {
        include_once _PS_MODULE_DIR_ . 'moloniprestashopes/src/WebHooks/WebserviceSpecificManagementMoloniProducts.php';

        return [
            'moloniproducts' => [
                'description' => 'My Custom Resource',
                'specific_management' => true,
            ],
        ];
    }

    /**
     * Add out CSS and JS files to the backend
     */
    public function hookActionAdminControllerSetMedia()
    {
        // Adds your's CSS file from a module's directory
        $this->context->controller->addCSS($this->_path . 'src/View/css/settingsStyle.css');
        $this->context->controller->addCSS($this->_path . 'src/View/css/all.min.css');
        $this->context->controller->addCSS($this->_path . 'src/View/css/moloni-icons.css');
        // Adds your's JavaScript file from a module's directory
        $this->context->controller->addJS($this->_path . 'src/View/js/settingsJS.js');
    }

    /**
     * Called after creating an product
     *
     * @param $params
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionProductAdd($params)
    {
        if ((Settings::get('AddProducts') == 1)) {
            $productSave = new ProductSave($this->context->getTranslator());
            $productSave->hookActionProductSave($params['id_product']);
        }
    }

    /**
     * Called after updating a product
     *
     * @param $params
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionProductUpdate($params)
    {
        if (((int) Settings::get('UpdateArtigos') === 1)) {
            $productSave = new ProductSave($this->context->getTranslator());
            $productSave->hookActionProductSave($params['id_product']);
        }
    }

    /**
     * Called after a order is paid
     *
     * @param $params
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     */
    public function hookActionPaymentConfirmation($params)
    {
        if (((int) Settings::get('CreateAuto') === 1)) {
            $paymentConfirmation = new PaymentConfirmation($this->context->getTranslator());
            $paymentConfirmation->hookActionPaymentConfirmation($params['id_order']);
        }
    }

    /**
     * Inits Autoload
     */
    private function autoload()
    {
        require_once __DIR__ . '/vendor/autoload.php';
    }
}
