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
use Moloni\Entity\MoloniApp;
use Moloni\Repository\MoloniAppRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
        'actionOrderStatusUpdate',
        'actionProductAdd',
        'actionProductUpdate',
        'actionUpdateQuantity',
        'actionGetAdminOrderButtons',
        'addWebserviceResources',
        'actionAdminProductsControllerSaveBefore',
    ];

    /**
     * Plugin tabs list
     *
     * @var array[]
     */
    private $tabs = [
        [
            'class_name' => 'Moloni',
            'parent_class_name' => 'SELL',
            'name' => [
                'en' => 'Moloni Spain',
                'es' => 'Moloni España',
            ],
            'wording' => 'Moloni Spain',
            'wording_domain' => 'Modules.Molonies.Admin',
            'icon' => 'logo',
        ],
        [
            'class_name' => 'MoloniOrders',
            'parent_class_name' => 'Moloni',
            'name' => [
                'en' => 'Orders',
                'es' => 'Pedidos pendientes',
            ],
            'wording' => 'Orders',
            'wording_domain' => 'Modules.Molonies.Admin',
            'icon' => '',
        ],
        [
            'class_name' => 'MoloniDocuments',
            'parent_class_name' => 'Moloni',
            'name' => [
                'en' => 'Documents',
                'es' => 'Documentos creados',
            ],
            'wording' => 'Documents',
            'wording_domain' => 'Modules.Molonies.Admin',
            'icon' => '',
        ],
        [
            'class_name' => 'MoloniSettings',
            'parent_class_name' => 'Moloni',
            'name' => [
                'en' => 'Settings',
                'es' => 'Configuraciones',
            ],
            'wording' => 'Settings',
            'wording_domain' => 'Modules.Molonies.Admin',
            'icon' => '',
        ],
        [
            'class_name' => 'MoloniTools',
            'parent_class_name' => 'Moloni',
            'name' => [
                'en' => 'Tools',
                'es' => 'Herramientas',
            ],
            'wording' => 'Tools',
            'wording_domain' => 'Modules.Molonies.Admin',
            'icon' => '',
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
     * @throws PrestaShopException
     */
    public function install(): bool
    {
        return $this->installTranslations()
            && $this->detectOldPluginTables()
            && $this->createCommon()
            && $this->importOldPluginDocuments();
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

        $newDocumentsTableDocuments = $database->executeS(
            "SELECT * FROM " . _DB_PREFIX_ . "moloni_order_documents LIMIT 1"
        );

        // New table already has documents, do not continue
        if (!empty($newDocumentsTableDocuments)) {
            return true;
        }

        $shopId = (int)Shop::getContextShopID();

        foreach ($oldDocumentsTableDocuments as $oldDocumentsTableDocument) {
            // Order is discarded in old plugin
            if ((int)$oldDocumentsTableDocument['invoice_status'] === 2) {
                $documentId = DocumentIdentifiers::DISCARDED;
            } else {
                $documentId = (int)$oldDocumentsTableDocument['document_id'];
            }

            $database->insert(
                'moloni_order_documents',
                [
                    'shop_id' => $shopId,
                    'company_id' => (int)$oldDocumentsTableDocument['company_id'],
                    'order_id' => (int)$oldDocumentsTableDocument['id_order'],
                    'document_id' => $documentId,
                    'order_reference' => $oldDocumentsTableDocument['order_ref'],
                    'document_type' => $oldDocumentsTableDocument['invoice_type'],
                    'created_at' => $oldDocumentsTableDocument['invoice_date'],
                ]
            );
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
            if (!$this->installTab($tab)) {
                return false;
            }
        }

        $this->registerHooks();

        return true;
    }

    /**
     * Common actions when uninstalling and disabling plugin
     *
     * @return bool
     */
    private function destroyCommon(): bool
    {
        $this->removeHooks();
        $this->removeLogin();

        return true;
    }

    /**
     * Installs an tab
     *
     * @param array $newTab
     *
     * @return bool
     */
    private function installTab(array $newTab): bool
    {
        $tabId = (int)Tab::getIdFromClassName($newTab['class_name']);
        $parentTabId = (int)Tab::getIdFromClassName($newTab['parent_class_name']);

        try {
            if (empty($tabId)) {
                $tabId = null;
            }

            $tab = new Tab($tabId);
            $tab->active = true;
            $tab->class_name = $newTab['class_name'];
            $tab->name = $this->getTabNames($newTab['name']);
            $tab->wording = $newTab['wording'];
            $tab->wording_domain = $newTab['wording_domain'];
            $tab->id_parent = $parentTabId;
            $tab->module = $this->module->name;
            $tab->icon = $newTab['icon'];

            return $tab->save();
        } catch (PrestaShopException $exception) {
            return false;
        }
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
            throw new RuntimeException(
                $this->module->getTranslator()->trans('Error loading installation files!', [], 'Modules.Molonies.Admin')
            );
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

    /**
     * Manually install translations on older instalations
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function installTranslations(): bool
    {
        // Instalation supports new translations loading, do nothing
        if (version_compare(_PS_VERSION_, '1.7.8', ">=")) {
            return true;
        }

        $database = Db::getInstance();

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'translation
                where `domain` like "ModulesMolonies%" LIMIT 1';

        $translations = $database->executeS($sql);

        // Translations already installed, do nothing
        if (!empty($translations)) {
            return true;
        }

        $languageId = Language::getIdByIso('ES');

        // Spain's language not installed, do nothing
        if ($languageId === null) {
            return true;
        }

        $translationsFile = glob($this->module->getLocalPath() . '/src/Install/sql/translations/es.sql');

        $sqlStatement = 'SET @idLang = ' . $languageId . ';' . PHP_EOL;
        $sqlStatement .= $this->getSqlStatements($translationsFile[0]);
        $sqlStatement = str_replace(["\n", "\r"], '', $sqlStatement);

        $database->execute($sqlStatement);

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
     * Get tab names
     *
     * @param array $names
     *
     * @return array
     */
    private function getTabNames(array $names): array
    {
        $translatedNames = [];

        foreach (Language::getLanguages() as $lang) {
            if (array_key_exists($lang['iso_code'], $names)) {
                /** Apply translated name */
                $translatedNames[$lang['id_lang']] = $names[$lang['iso_code']];
            } else {
                /** Get the first name available in the array */
                $translatedNames[$lang['id_lang']] = reset($names);
            }
        }

        return $translatedNames;
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

    private function registerHooks(): void
    {
        foreach ($this->hooks as $hookName) {
            if (!$this->module->registerHook($hookName)) {
                return;
            }
        }
    }

    private function removeHooks(): void
    {
        foreach ($this->hooks as $hookName) {
            try {
                $name = Hook::getIdByName($hookName);
            } catch (PrestaShopDatabaseException $e) {
                continue;
            }

            if (!$this->module->unregisterHook($name)) {
                return;
            }
        }
    }
}
