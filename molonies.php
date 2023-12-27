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

use Doctrine\Common\Persistence\ManagerRegistry as LegacyManagerRegistry;
use Doctrine\Persistence\ManagerRegistry;
use Moloni\Exceptions\MoloniException;
use Moloni\Hooks\AdminOrderButtons;
use Moloni\Hooks\OrderStatusUpdate;
use Moloni\Hooks\ProductSave;
use Moloni\Hooks\ProductStockUpdate;
use Moloni\Install\Installer;
use Moloni\Services\MoloniContext;
use Moloni\Tools\SyncLogs;
use PrestaShopBundle\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class MoloniEs extends Module
{
    /** @var bool */
    private $openForHookQuantityUpdate = false;

    /**
     * Molonies constructor.
     */
    public function __construct()
    {
        $this->name = 'molonies';
        $this->tab = 'administration';

        $this->need_instance = 1;
        $this->version = '2.3.62';
        $this->ps_versions_compliancy = ['min' => '1.7.6', 'max' => _PS_VERSION_];
        $this->author = 'Moloni';
        $this->module_key = '63e30380b2942ec15c33bedd4f7ec90e';

        parent::__construct();

        $this->displayName = $this->trans('Moloni Spain', [], 'Modules.Molonies.Molonies');
        $this->description = $this->trans(
            'Automatic document creation with real time stock synchronization and powerful sales analysis.',
            [],
            'Modules.Molonies.Molonies'
        );
        $this->confirmUninstall = $this->trans(
            'Are you sure you want to unnistall this module?',
            [],
            'Modules.Molonies.Molonies'
        );

        $this->autoload();
    }

    /**
     * Plugin settings shortcut
     *
     * @return void
     */
    public function getContent(): void
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('MoloniSettings'));
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
        /** @url https://devdocs.prestashop-project.org/8/modules/creation/tutorial/#the-install-method */
        if (Shop::isFeatureActive()) {
            try {
                Shop::setContext(Shop::CONTEXT_ALL);
            } catch (PrestaShopException $e) {}
        }

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
     * Add out CSS and JS files to the backend
     *
     * @return void
     */
    public function hookActionAdminControllerSetMedia(): void
    {
        $action = $this->context->controller->php_self ?? '';

        $this->context->controller->addCSS($this->_path . 'views/css/moloni-icons.css');

        if (strpos($action, 'Moloni') === 0) {
            $this->context->controller->addJS($this->_path . 'views/js/app.js?v=' . $this->version);
            $this->context->controller->addCSS($this->_path . 'views/css/app.css');
        }
    }

    /**
     * Add endpoints to Prestashop Webservices
     *
     * @return array[]
     */
    public function hookAddWebserviceResources(): array
    {
        try {
            $this->initContext();
        } catch (Exception $e) {
            return [];
        }

        include_once _PS_MODULE_DIR_ . 'molonies/src/Webservice/WebserviceSpecificManagementMoloniResource.php';

        return [
            'moloniresource' => [
                'description' => 'Moloni sync resource',
                'specific_management' => true,
            ],
        ];
    }

    public function hookActionAdminProductsControllerSaveBefore(): void
    {
        $productId = (int)$_POST['id_product'];
        if ($productId > 0) {
            try {
                $this->initContext();
                SyncLogs::prestashopProductRemoveTimeout($productId);
            } catch (Exception $e) {
                // Do nothing
            }
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
            $this->initContext();
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
            $this->initContext();
            new ProductSave($params['id_product']);
            $this->openForHookQuantityUpdate = true;
        } catch (Exception $e) {
            // Do nothing
        }
    }

    public function hookActionUpdateQuantity($params): bool
    {
        try {
            if ($this->openForHookQuantityUpdate) {
                $this->initContext();

                new ProductStockUpdate(
                    (int)$params['id_product'],
                    (int)$params['id_product_attribute'],
                    (float)$params['quantity']
                );
            }
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
            $this->initContext();

            /** @var ManagerRegistry|LegacyManagerRegistry $doctrine */
            $doctrine = $this->get('doctrine');

            new OrderStatusUpdate($params['id_order'], $params['newOrderStatus'], $doctrine->getManager());
        } catch (Exception $e) {
            // Do nothing
        }
    }

    /**
     * displayAdminOrderTop
     */
    public function hookActionGetAdminOrderButtons($params): void
    {
        try {
            $this->initContext();

            /** @var ManagerRegistry|LegacyManagerRegistry $doctrine */
            $doctrine = $this->get('doctrine');
            /** @var Router $router */
            $router = $this->get('router');
            /** @var TranslatorInterface $translator */
            $translator = $this->getTranslator();

            new AdminOrderButtons($params, $router, $doctrine, $translator);
        } catch (Exception $e) {
            // Do nothing
        }
    }

    //          Privates          //

    /**
     * Init Moloni plugin context for in hooks
     *
     * @throws Exception
     */
    private function initContext(): void
    {
        /** @var ManagerRegistry|LegacyManagerRegistry|false $doctrine */
        $doctrine = $this->get('doctrine');

        if (empty($doctrine)) {
            $doctrine = $this->getContainer()->get('doctrine');
        }

        if (empty($doctrine)) {
            throw new MoloniException('Error loading doctrine');
        }

        new MoloniContext($doctrine->getManager());
    }

    /**
     * Inits Autoload
     */
    private function autoload(): void
    {
        require_once __DIR__ . '/vendor/autoload.php';
    }
}
