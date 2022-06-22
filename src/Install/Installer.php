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
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

namespace Moloni\Install;

use DateTime;
use Db;
use Moloni\Enums\DocumentIdentifiers;
use Shop;
use Tab;
use Hook;
use Tools;
use Language;
use MoloniEs;
use Exception;
use RuntimeException;
use PrestaShopException;
use PrestaShopDatabaseException;
use Doctrine\Persistence\ObjectManager;
use Moloni\Entity\MoloniApp;
use Moloni\Entity\MoloniOrderDocuments;
use Moloni\Repository\MoloniAppRepository;
use Moloni\Repository\MoloniOrderDocumentsRepository;

class Installer
{
    /**
     * The module data
     *
     * @var MoloniEs
     */
    private $module;

    /**
     * Hooks list
     *
     * @var string[]
     */
    private $hooks = [
        'actionAdminControllerSetMedia',
        'actionPaymentConfirmation',
        'actionOrderStatusUpdate',
        'actionProductAdd',
        'actionProductUpdate',
        'actionGetAdminOrderButtons',
        'addWebserviceResources',
    ];

    /**
     * Tabs list
     *
     * @var array[]
     */
    private $tabs = [
        [
            'name' => 'Moloni',
            'parent' => 'SELL',
            'tabName' => 'Moloni Spain',
            'logo' => 'logo',
        ], [
            'name' => 'MoloniOrders',
            'parent' => 'Moloni',
            'tabName' => 'Orders',
            'logo' => '',
        ], [
            'name' => 'MoloniDocuments',
            'parent' => 'Moloni',
            'tabName' => 'Documents',
            'logo' => '',
        ], [
            'name' => 'MoloniSettings',
            'parent' => 'Moloni',
            'tabName' => 'Settings',
            'logo' => '',
        ], [
            'name' => 'MoloniTools',
            'parent' => 'Moloni',
            'tabName' => 'Tools',
            'logo' => '',
        ],
    ];

    /**
     * Installer constructor.
     */
    public function __construct(MoloniEs $module)
    {
        $this->module = $module;
    }

    /**
     * Install plugin
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function install(): bool
    {
        return $this->detectOldPluginTables() && $this->createCommon() && $this->importOldPluginDocuments();
    }

    /**
     * Enable plugin
     *
     * @return bool
     */
    public function enable(): bool
    {
        return $this->createCommon();
    }

    /**
     * Uninstall plugin
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        return $this->destroyCommon();
    }

    /**
     * Disable plugin
     *
     * @return bool
     */
    public function disable(): bool
    {
        return $this->destroyCommon();
    }

    //        OLD PLUGIN ACTIONS        //

    /**
     * Some verifications to prevent old plugin errors
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    private function detectOldPluginTables(): bool
    {
        $database = Db::getInstance();

        // Check if the table already exists
        $query = $database->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "moloni_app` LIKE 'id'");

        if (empty($query)) {
            return true;
        }

        // If so, check if new columns exist
        $query = $database->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "moloni_app` LIKE 'access_time'");

        if (!empty($query)) {
            return true;
        }

        // If not, drop old tables
        $database->execute('DROP TABLE ' . _DB_PREFIX_ . 'moloni_app');
        $database->execute('DROP TABLE ' . _DB_PREFIX_ . 'moloni_settings');
        $database->execute('DROP TABLE ' . _DB_PREFIX_ . 'moloni_sync_logs');

        return true;
    }

    /**
     * Imports old plugin documents to new plugin structure
     *
     * @return bool
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     */
    private function importOldPluginDocuments(): bool
    {
        $database = Db::getInstance();

        $oldDocumentsTableExist = $database->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . "moloni_documents'");

        // Old documents table not found, do not continue
        if (empty($oldDocumentsTableExist)) {
            return true;
        }

        $oldDocumentsTableDocuments = $database->executeS("SELECT * FROM " . _DB_PREFIX_ . "moloni_documents");

        // Old table has no documents, do not continue
        if (empty($oldDocumentsTableDocuments)) {
            return true;
        }

        /** @var MoloniOrderDocumentsRepository $orderDocumentsRepository */
        $orderDocumentsRepository = $this->module->get('doctrine')->getRepository(MoloniOrderDocuments::class);

        // New table already has documents, do not continue
        if (!empty($orderDocumentsRepository->findOneBy([]))) {
            return true;
        }

        /** @var ObjectManager $entityManager */
        $entityManager = $this->module->get('doctrine')->getManager();
        $shopId = (int)Shop::getContextShopID();

        foreach ($oldDocumentsTableDocuments as $oldDocumentsTableDocument) {
            $document = new MoloniOrderDocuments();

            $document->setShopId($shopId);
            $document->setCompanyId((int)$oldDocumentsTableDocument['company_id']);
            $document->setOrderId((int)$oldDocumentsTableDocument['id_order']);

            // Order is discarded in old plugin
            if ((int)$oldDocumentsTableDocument['invoice_status'] === 2) {
                $document->setDocumentId(DocumentIdentifiers::DISCARDED);
            } else {
                $document->setDocumentId((int)$oldDocumentsTableDocument['document_id']);
            }

            $document->setOrderReference($oldDocumentsTableDocument['order_ref']);
            $document->setDocumentType($oldDocumentsTableDocument['invoice_type']);
            $document->setCreatedAt(new DateTime($oldDocumentsTableDocument['invoice_date']));

            // Save entry
            $entityManager->persist($document);
            $entityManager->flush();
        }

        return true;
    }

    //        PRIVATES        //

    /**
     * Common actions when installing and enabling plugin
     *
     * @return bool
     */
    private function createCommon(): bool
    {
        if (!$this->installDb()) {
            return false;
        }

        foreach ($this->tabs as $tab) {
            $tabName = $this->module->getTranslator()->trans(
                $tab['tabName'],
                [],
                'Modules.Molonies.Admin'
            );

            if (!$this->installTab($tab['name'], $tab['parent'], $tabName, $tab['logo'])) {
                return false;
            }
        }

        foreach ($this->hooks as $hookName) {
            if (!$this->module->registerHook($hookName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Common actions when uninstalling and disabling plugin
     *
     * @return bool
     */
    private function destroyCommon(): bool
    {
        foreach ($this->tabs as $tab) {
            if (!$this->uninstallTab($tab['name'])) {
                return false;
            }
        }

        foreach ($this->hooks as $hookName) {
            try {
                $name = Hook::getIdByName($hookName);
            } catch (PrestaShopDatabaseException $e) {
                continue;
            }

            if (!$this->module->unregisterHook($name)) {
                return false;
            }
        }

        $this->removeLogin();

        return true;
    }

    /**
     * Installs an tab
     *
     * @param string $className
     * @param string $parentClassName
     * @param string $tabName
     * @param string $logo
     *
     * @return bool
     */
    private function installTab(string $className, string $parentClassName, string $tabName, string $logo): bool
    {
        try {
            $tabId = (int) Tab::getIdFromClassName($className);

            if (!$tabId) {
                $tabId = null;
            }

            $tab = new Tab($tabId);
            $tab->active = true;
            $tab->class_name = $className;
            $tab->name = [];

            foreach (Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = $tabName;
            }

            $tab->id_parent = (int) Tab::getIdFromClassName($parentClassName);
            $tab->module = $this->module->name;

            if (!empty($logo)) {
                $tab->icon = $logo;
            }

            return $tab->save();
        } catch (PrestaShopException $exception) {
            return false;
        }
    }

    /**
     * Deletes an tab
     *
     * @param string $className
     *
     * @return bool
     */
    private function uninstallTab(string $className): bool
    {
        try {
            $tabId = (int) Tab::getIdFromClassName($className);

            if ($tabId) {
                (new Tab($tabId))->delete();
            }
        } catch (PrestaShopException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Reads sql files and executes
     *
     * @return bool
     */
    private function installDb(): bool
    {
        $installSqlFiles = glob($this->module->getLocalPath() . '/src/Install/sql/install/*.sql');

        if (empty($installSqlFiles)) {
            throw new RuntimeException($this->module->getTranslator()->trans('Error loading installation files!', [], 'Modules.Molonies.Admin'));
        }

        $database = Db::getInstance();

        foreach ($installSqlFiles as $sqlFile) {
            $sqlStatements = $this->getSqlStatements($sqlFile);
            try {
                $database->execute($sqlStatements);
            } catch (Exception $exception) {
                $parts = explode('/', $sqlFile);
                $msg = $this->module->getTranslator()->trans(
                    'Error executing operation from %s!',
                    [],
                    'Modules.Molonies.Admin'
                );

                throw new RuntimeException(sprintf($msg, end($parts)));
            }
        }

        return true;
    }

    //        AUXILIARY       //

    /**
     * Loads databases query´s
     *
     * @param string $fileName
     *
     * @return string
     */
    private function getSqlStatements(string $fileName): string
    {
        $sqlStatements = Tools::file_get_contents($fileName);

        return str_replace(['PREFIX_', 'ENGINE_TYPE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sqlStatements);
    }

    /**
     * Remove login credentials
     *
     * @return void
     */
    private function removeLogin(): void
    {
        try {
            /** @var MoloniAppRepository $repository */
            $repository = $this
                ->module
                ->get('doctrine')
                ->getRepository(MoloniApp::class);

            $repository->deleteApp();
        } catch (Exception $e) {
            // No need to catch
        }
    }
}
