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

use Moloni\Hooks\ProductSave;
use Moloni\Hooks\AdminOrderButtons;
use Moloni\Hooks\OrderStatusUpdate;
use Moloni\Install\Installer;
use Moloni\Services\MoloniContext;
use PrestaShopBundle\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Doctrine\Common\Persistence\ManagerRegistry as LegacyManagerRegistry;
use Doctrine\Persistence\ManagerRegistry;

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
        $this->version = '2.0.37';
        $this->ps_versions_compliancy = ['min' => '1.7.6', 'max' => _PS_VERSION_];
        $this->author = 'Moloni';

        parent::__construct();

        $this->displayName = $this->l('Moloni España');
        $this->description = $this->l('Automatic document creation with real time stock synchronization and powerful sales analysis.');
        $this->confirmUninstall = $this->trans(
            'Are you sure you want to unnistall this module?',
            [],
            'Modules.Molonies.Admin'
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
            $this->context->controller->addJS($this->_path . 'views/js/app.js');
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
            // todo: catch this?
        }

        include_once _PS_MODULE_DIR_ . 'molonies/src/Webservice/WebserviceSpecificManagementMoloniResource.php';

        return [
            'moloniresource' => [
                'description' => 'Moloni sync resource',
                'specific_management' => true,
            ],
        ];
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
        } catch (Exception $e) {
            // Do nothing
        }
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
            /** @var TranslatorInterface  $translator */
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
        /** @var ManagerRegistry|LegacyManagerRegistry $doctrine */
        $doctrine = $this->get('doctrine');

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
