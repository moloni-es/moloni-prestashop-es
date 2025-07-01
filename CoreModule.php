<?php

/**
 * 2025 - Moloni.com
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

use Moloni\Activators\Install;
use Moloni\Activators\Remove;
use Moloni\Enums\MoloniRoutes;
use Moloni\Hooks\AdminOrderButtons;
use Moloni\Hooks\OrderStatusUpdate;
use Moloni\Hooks\ProductSave;
use Moloni\Hooks\ProductStockUpdate;
use Moloni\MoloniContext;
use Moloni\Tools\SyncLogs;

include_once __DIR__ . '/src/Webservice/WebserviceSpecificManagementMoloniResource.php';

class CoreModule extends Module
{
    /** @var bool */
    protected $openForHookQuantityUpdate = false;

    /**
     * Plugin settings shortcut
     *
     * @return void
     */
    public function getContent(): void
    {
        try {
            $router = $this->get('router');
        } catch (Exception $e) {
            return;
        }

        $url = $router->generate(MoloniRoutes::SETTINGS);

        Tools::redirectAdmin($url);
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
        if (Shop::isFeatureActive()) {
            try {
                Shop::setContext(ShopCore::CONTEXT_ALL);
            } catch (PrestaShopException $e) {
            }
        }

        if (!parent::install()) {
            return false;
        }

        try {
            $service = new Install($this);
            $service->install();
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
            $service = new Remove($this);
            $service->uninstall();
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
            $service = new Install($this);
            $service->enable();
        } catch (Exception $exception) {
            $this->_errors[] = $exception->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Disable plugin
     *
     * @param bool|null $force_all
     *
     * @return bool
     */
    public function disable($force_all = false): bool
    {
        if (!parent::disable($force_all)) {
            return false;
        }

        try {
            $service = new Remove($this);
            $service->disable();
        } catch (Exception $exception) {
            $this->_errors[] = $exception->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Add out CSS and JS files to the backend
     *
     * @return void
     */
    public function hookActionAdminControllerSetMedia(): void
    {
        $action = $this->context->controller->php_self ?? '';

        $this->context->controller->addCSS("{$this->_path}views/css/moloni-icons.css");

        if (strpos($action, 'Moloni') !== 0) {
            return;
        }

        $this->context->controller->addJS("{$this->_path}views/js/app.js?v={$this->version}");
        $this->context->controller->addCSS("{$this->_path}views/css/app.css?v={$this->version}");
    }

    /**
     * Add endpoints to Prestashop Webservices
     *
     * @return array[]
     */
    public function hookAddWebserviceResources(): array
    {
        try {
            $this->getContext();
        } catch (Exception $e) {
            // catch nothing
        }

        return [
            'molonionresource' => [
                'description' => 'MoloniOn sync resource',
                'specific_management' => true,
            ],
        ];
    }

    public function hookActionAdminProductsControllerSaveBefore(): void
    {
        $productId = (int) $_POST['id_product'];

        if ($productId <= 0) {
            return;
        }

        try {
            $this->getContext();

            SyncLogs::prestashopProductRemoveTimeout($productId);
        } catch (Exception $e) {
            // Do nothing
        }
    }

    /**
     * Called after creating a product
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionProductAdd(array $params): void
    {
        try {
            $this->getContext();

            new ProductSave($params['id_product']);

            $this->openForHookQuantityUpdate = true;
        } catch (Exception $e) {
            // Do nothing
        }
    }

    /**
     * Called after updating a product
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionProductUpdate(array $params): void
    {
        try {
            $this->getContext();

            new ProductSave($params['id_product']);

            $this->openForHookQuantityUpdate = true;
        } catch (Exception $e) {
            // Do nothing
        }
    }

    public function hookActionUpdateQuantity($params): bool
    {
        if (!$this->openForHookQuantityUpdate) {
            return true;
        }

        try {
            $this->getContext();

            new ProductStockUpdate(
                (int) $params['id_product'],
                (int) $params['id_product_attribute'],
                (float) $params['quantity']
            );
        } catch (Exception $e) {
            // Do nothing
        }

        return true;
    }

    /**
     * Called after an order is paid
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionOrderStatusUpdate(array $params): void
    {
        try {
            /** @var MoloniContext|false $context */
            $context = $this->getContext();
        } catch (Exception $e) {
            // Do nothing
        }

        if (!$context) {
            return;
        }

        new OrderStatusUpdate($params['id_order'], $params['newOrderStatus'], $context);
    }

    /**
     * displayAdminOrderTop
     */
    public function hookActionGetAdminOrderButtons($params): void
    {
        try {
            /** @var MoloniContext|false $context */
            $context = $this->getContext();

            if (!$context) {
                return;
            }

            new AdminOrderButtons($params, $context);
        } catch (Exception $e) {
            // Do nothing
        }
    }

    //          Privates          //

    /**
     * Init Moloni plugin context for in hooks
     *
     * @return false|object
     *
     * @throws Exception
     */
    protected function getContext()
    {
        return $this->get('moloni.context');
    }

    /**
     * Inits Autoload
     */
    protected function autoload(): void
    {
        require_once __DIR__ . '/vendor/autoload.php';
    }
}
