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
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

namespace Moloni\Install;

use Db;
use Exception;
use Hook;
use Language;
use MoloniEs;
use PrestaShopDatabaseException;
use PrestaShopException;
use RuntimeException;
use Tab;
use Tools;

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
        'actionProductAdd',
        'actionProductUpdate',
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
     */
    public function install(): bool
    {
        //if (!$this->installTranslations()) {
        //    return false;
        //}

        return $this->createCommon();
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
        if (!$this->uninstallTranslations()) {
            return false;
        }

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

    //        Privates        //

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
                'Modules.Molonies.Molonies'
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
     * Reads sql files and executes
     *
     * @return bool
     */
    private function installDb(): bool
    {
        $installSqlFiles = glob($this->module->getLocalPath() . '/src/Install/sql/install/*.sql');

        $arg1 = 'Error loading installation files!';
        $arg2 = [];
        $arg3 = 'Modules.Molonies.Molonies';

        if (empty($installSqlFiles)) {
            throw new RuntimeException($this->module->getTranslator()->trans($arg1, $arg2, $arg3));
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
                    'Modules.Molonies.Molonies'
                );

                throw new RuntimeException(sprintf($msg, end($parts)));
            }
        }

        return true;
    }

    /**
     * Install translations
     *
     * @return bool
     */
    private function installTranslations(): bool
    {
        $database = Db::getInstance();

        // verify if the translations already exist
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'translation
                where `domain` like "ModulesMolonies%"';

        try {
            $count = (int) ($database->executeS($sql))[0]['count(*)'];
        } catch (PrestaShopDatabaseException $e) {
            return true;
        }

        if ($count === 0) {
            return true;
        }

        $langs = ['PT', 'ES'];

        foreach ($langs as $lang) {
            try {
                $langId = Language::getIdByIso($lang);
            } catch (PrestaShopException $e) {
                return true;
            }

            if ($langId) {
                $sqlFile = glob($this->module->getLocalPath() . 'sql/translations/' . strtolower($lang) . '.sql');

                $sqlStatement = 'SET @idLang = ' . $langId . ';' . PHP_EOL;
                $sqlStatement .= $this->getSqlStatements($sqlFile[0]);

                try {
                    $database->execute($sqlStatement);
                } catch (Exception $exception) {
                    return false;
                }
            }
        }

        return true;
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
     * Uninstalls translations
     *
     * @return bool
     */
    private function uninstallTranslations(): bool
    {
        $database = Db::getInstance();
        $database->delete(
            'translation',
            '`domain` LIKE \'ModulesMolonies%\''
        );

        return true;
    }

    /**
     * Remove login credentials
     *
     * @return void
     */
    private function removeLogin(): void
    {
        $dataBase = \Db::getInstance();
        $dataBase->execute('TRUNCATE ' . _DB_PREFIX_ . 'moloni_app');
    }
}
