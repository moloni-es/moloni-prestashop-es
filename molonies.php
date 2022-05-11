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
use Moloni\Hooks\ProductAdd;
use Moloni\Hooks\ProductUpdate;
use Moloni\Install\Installer;

class MoloniEs extends Module
{
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
            'Transform all your orders in verified documents without any effort and focus on selling!',
            [],
            'Modules.Molonies.Molonies'
        );
        $this->confirmUninstall = $this->trans(
            'Do you want to unnistall module? All information will be deleted!',
            [],
            'Modules.Molonies.Molonies'
        );

        $this->autoload();
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
        if (!parent::install()) {
            return false;
        }

        try {
            if (!(new Installer($this))->install()) {
                return false;
            }
        } catch (Exception $exception) {
            $this->_errors[] = $exception->getMessage();

            return false;
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
        if (!parent::uninstall()) {
            return false;
        }

        try {
            if (!(new Installer($this))->uninstall()) {
                return false;
            }
        } catch (Exception $exception) {
            $this->_errors[] = $exception->getMessage();

            return false;
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
        if (!parent::enable($force_all)) {
            return false;
        }

        try {
            if (!(new Installer($this))->enable()) {
                return false;
            }
        } catch (Exception $exception) {
            $this->_errors[] = $exception->getMessage();

            return false;
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
    public function disable($force_all = false): bool
    {
        if (!parent::disable($force_all)) {
            return false;
        }

        try {
            if (!(new Installer($this))->disable()) {
                return false;
            }
        } catch (Exception $exception) {
            $this->_errors[] = $exception->getMessage();

            return false;
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
        include_once _PS_MODULE_DIR_ . 'molonies/src/WebHooks/WebserviceSpecificManagementMoloniResource.php';

        return [
            'moloniresource' => [
                'description' => 'Moloni sync resource',
                'specific_management' => true,
            ],
        ];
    }

    /**
     * Add out CSS and JS files to the backend
     *
     * @return void
     */
    public function hookActionAdminControllerSetMedia(): void
    {
        // Adds yours CSS files from a module's directory
        //$this->context->controller->addCSS($this->_path . 'views/css/all.min.css');
        $this->context->controller->addCSS($this->_path . 'views/css/moloni.css');
        $this->context->controller->addCSS($this->_path . 'views/css/moloni-icons.css');

        // Adds yours JavaScript files from a module's directory
        $this->context->controller->addJS($this->_path . 'views/js/settingsJS.js');

        // Deprecated??
        // $this->context->controller->addJquery();
    }

    /**
     * Called after creating an product
     *
     * @param $params
     *
     * @return void
     */
    public function hookActionProductAdd($params): void
    {
        if ((Settings::get('AddProducts') == 1)) {
            $productSave = new ProductAdd($this->context->getTranslator());
            $productSave->hookActionProductSave($params['id_product']);
        }
    }

    /**
     * Called after updating a product
     *
     * @param $params
     *
     * @return void
     */
    public function hookActionProductUpdate($params): void
    {
        $productSave = new ProductUpdate($this->context->getTranslator());
        $productSave->hookActionProductSave($params['id_product']);
    }

    /**
     * Called after a order is paid
     *
     * @param $params
     *
     * @return void
     */
    public function hookActionPaymentConfirmation($params): void
    {
        $paymentConfirmation = new OrderPaid($this->context->getTranslator());
        $paymentConfirmation->hookActionPaymentConfirmation($params['id_order']);
    }

    /**
     * Inits Autoload
     */
    private function autoload(): void
    {
        require_once __DIR__ . '/vendor/autoload.php';
    }
}
