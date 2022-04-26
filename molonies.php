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

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

use Moloni\Helpers\Settings;
use Moloni\Hooks\OrderPaid;
use Moloni\Hooks\ProductSave;
use Moloni\Install\Installer;

class MoloniEs extends Module
{
    /**
     * Configuration data teste 2
     *
     * @var string[][]
     */
    private $configuration = [];

    /**
     * Hooks list
     *
     * @var string[]
     */
    private $hooks = [
        'actionAdminControllerSetMedia',
        'actionPaymentConfirmation',
        'actionProductAdd',
        'actionProductUpdate',
        'addWebserviceResources',
    ];

    /**
     * Molonies constructor.
     */
    public function __construct()
    {
        $this->name = 'molonies';
        $this->tab = 'administration';

        $this->need_instance = 1;
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.7.6', 'max' => _PS_VERSION_];
        $this->author = 'Moloni';

        parent::__construct();

        $this->displayName = $this->trans('Moloni España', [], 'Modules.Molonies.Molonies');
        $this->description = $this->trans(
            'Transform all your orders in verified documents
        without any effort and focus on selling!',
            [],
            'Modules.Molonies.Molonies'
        );
        $this->confirmUninstall = $this->trans(
            'Do you want to unnistall module? All information will be deleted!',
            [],
            'Modules.Molonies.Molonies'
        );
    }

    /**
     * Enable new translations module
     *
     * @return bool
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /**
     * Install plugin
     *
     * @return bool
     */
    public function install(): bool
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
    public function uninstall(): bool
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
    public function enable($force_all = false): bool
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
    public function hookAddWebserviceResources(): array
    {
        include_once _PS_MODULE_DIR_ . 'molonies/src/WebHooks/WebserviceSpecificManagementMoloniProducts.php';

        return [
            'moloniproducts' => [
                'description' => 'Moloni sync resource',
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

        return true;
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
            $paymentConfirmation = new OrderPaid($this->context->getTranslator());
            $paymentConfirmation->hookActionPaymentConfirmation($params['id_order']);
        }
    }
}
