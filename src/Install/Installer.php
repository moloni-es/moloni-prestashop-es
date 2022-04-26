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
use Language;
use PrestaShopException;
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
     * Configuration data
     *
     * @var array
     */
    private $configuration;

    /**
     * Installer constructor.
     */
    public function __construct(MoloniEs $module, array $configuration)
    {
        $this->module = $module;
        $this->configuration = $configuration;
    }

    /**
     * Install plugin
     *
     * @return bool
     *
     * @throws Exception
     */
    public function install()
    {
        if (!$this->installTranslations()) {
            return false;
        }

        if (!$this->installDb()) {
            return false;
        }

        if (!$this->installTab('Moloni', 'SELL', 'Moloni', 'logo')
            || !$this->installTab('MoloniHome', 'Moloni', $this->module->getTranslator()->trans(
                'Orders',
                [],
                'Modules.Molonies.Molonies'
            ), '')
            || !$this->installTab('MoloniDocuments', 'Moloni', $this->module->getTranslator()->trans(
                'Documents',
                [],
                'Modules.Molonies.Molonies'
            ), '')
            || !$this->installTab('MoloniSettings', 'Moloni', $this->module->getTranslator()->trans(
                'Settings',
                [],
                'Modules.Molonies.Molonies'
            ), '')) {
            return false;
        }

        return true;
    }

    /**
     * Enable plugin
     *
     * @return bool
     *
     * @throws Exception
     */
    public function enable()
    {
        if (!$this->installDb()) {
            return false;
        }

        if (!$this->installTab('Moloni', 'SELL', 'Moloni', 'logo')
            || !$this->installTab('MoloniHome', 'Moloni', $this->module->getTranslator()->trans(
                'Orders',
                [],
                'Modules.Molonies.Molonies'
            ), '')
            || !$this->installTab('MoloniDocuments', 'Moloni', $this->module->getTranslator()->trans(
                'Documents',
                [],
                'Modules.Molonies.Molonies'
            ), '')
            || !$this->installTab('MoloniSettings', 'Moloni', $this->module->getTranslator()->trans(
                'Settings',
                [],
                'Modules.Molonies.Molonies'
            ), '')) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall plugin
     *
     * @return bool
     *
     * @throws Exception
     */
    public function uninstall()
    {
        if (!$this->uninstallTranslations()) {
            return false;
        }

        if (!$this->uninstallTab('Moloni')
            || !$this->uninstallTab('MoloniHome')
            || !$this->uninstallTab('MoloniDocuments')
            || !$this->uninstallTab('MoloniSettings')) {
            return false;
        }

        $this->removeLogin();

        return true;
    }

    /**
     * Disable plugin
     *
     * @return bool
     *
     * @throws Exception
     */
    public function disable()
    {
        if (!$this->uninstallTab('Moloni')
            || !$this->uninstallTab('MoloniHome')
            || !$this->uninstallTab('MoloniDocuments')
            || !$this->uninstallTab('MoloniSettings')) {
            return false;
        }

        $this->removeLogin();

        return true;
    }

    /**
     * Loads databases query´s
     *
     * @param $fileName
     *
     * @return bool|string|string[]
     */
    public function getSqlStatements($fileName)
    {
        $sqlStatements = Tools::file_get_contents($fileName);
        $sqlStatements = str_replace(['PREFIX_', 'ENGINE_TYPE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sqlStatements);

        return $sqlStatements;
    }

    /**
     * Installs an tab
     *
     * @param string $className
     * @param string $parentClassName
     * @param string $tabName
     *
     * @return bool
     */
    private function installTab($className, $parentClassName, $tabName, $logo)
    {
        try {
            $tabId = (int) Tab::getIdFromClassName($className);

            if (!$tabId) {
                $tabId = null;
            }

            $tab = new Tab($tabId);
            $tab->active = 1;
            $tab->class_name = $className;
            $tab->name = [];
            foreach (Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = $tabName;
            }
            $tab->id_parent = (int) Tab::getIdFromClassName($parentClassName);
            $tab->module = 'Molonies';
            if ($logo != '') {
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
     *
     * @throws PrestaShopException
     */
    private function uninstallTab($className)
    {
        try {
            $tabId = (int) Tab::getIdFromClassName($className);
            if (!$tabId) {
                return true;
            }

            $tab = new Tab($tabId);

            return $tab->delete();
        } catch (PrestaShopException $exception) {
            return false;
        }
    }

    /**
     * Reads sql files and executes
     *
     * @return bool
     *
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD)
     */
    private function installDb()
    {
        $installSqlFiles = glob($this->module->getLocalPath() . 'sql/install/*.sql');
        $arg1 = 'Error loading installation files!';
        $arg2 = [];
        $arg3 = 'Modules.Molonies.Molonies';

        if (empty($installSqlFiles)) {
            throw new Exception($this->module->getTranslator()->trans($arg1, $arg2, $arg3));
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
                throw new Exception(sprintf($msg, end($parts)));
            }
        }

        return true;
    }

    /**
     * Install translations
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    private function installTranslations()
    {
        $database = Db::getInstance();

        // verify if the translations already exist
        $sql = 'SELECT count(*) FROM ' . _DB_PREFIX_ . 'translation
                where `domain` like "ModulesMolonies%"';
        $count = (int) ($database->executeS($sql))[0]['count(*)'];

        if ($count != 0) {
            return true;
        }

        $langs = ['PT', 'ES'];

        foreach ($langs as $lang) {
            if (\Language::getIdByIso($lang) != false) {
                $sqlFile = glob($this->module->getLocalPath() . 'sql/translations/' . strtolower($lang) . '.sql');

                $sqlStatement = 'SET @idLang = ' . \Language::getIdByIso($lang) . ';' . PHP_EOL;
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
     * Uninstalls translations
     *
     * @return bool
     */
    private function uninstallTranslations()
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
     * @return bool
     */
    public function removeLogin()
    {
        $dataBase = \Db::getInstance();
        $dataBase->execute('TRUNCATE ' . _DB_PREFIX_ . 'moloni_app');

        return true;
    }
}
