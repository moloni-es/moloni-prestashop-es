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

namespace Moloni\Activators;

use CoreModule;
use Moloni\Configurations;
use Moloni\Exceptions\MoloniActivatorException;
use Tab;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Install extends ActivatorAbstract
{
    /**
     * Installer constructor.
     */
    public function __construct(CoreModule $module)
    {
        $this->module = $module;
    }

    /**
     * Install plugin
     *
     * @return void
     *
     * @throws MoloniActivatorException
     */
    public function install(): void
    {
        $this->createCommon();
    }

    /**
     * Enable plugin
     *
     * @return void
     *
     * @throws MoloniActivatorException
     */
    public function enable(): void
    {
        $this->createCommon();
    }

    //        PRIVATES        //

    /**
     * Common actions when installing and enabling plugin
     *
     * @return void
     *
     * @throws MoloniActivatorException
     */
    private function createCommon(): void
    {
        $this->installDatabase();

        foreach ($this->tabs as $tab) {
            $this->installTab($tab);
        }

        $this->registerHooks();
    }

    /**
     * Installs an tab
     *
     * @param array $newTab
     *
     * @return void
     *
     * @throws MoloniActivatorException
     */
    private function installTab(array $newTab): void
    {
        $tabId = (int) \Tab::getIdFromClassName($newTab['class_name']);
        $parentTabId = (int) \Tab::getIdFromClassName($newTab['parent_class_name']);

        try {
            if (empty($tabId)) {
                $tabId = null;
            }

            $tab = new \Tab($tabId);
            $tab->active = true;
            $tab->class_name = $newTab['class_name'];
            $tab->name = $this->getTabNames($newTab['name']);
            $tab->wording = $newTab['wording'];
            $tab->wording_domain = $newTab['wording_domain'];
            $tab->id_parent = $parentTabId;
            $tab->module = $this->module->name;
            $tab->icon = $newTab['icon'];

            $tab->save();
        } catch (\PrestaShopException $exception) {
            throw new MoloniActivatorException("Error installing tab! ({$newTab['class_name']})");
        }
    }

    /**
     * Reads sql files and executes
     *
     * @return void
     *
     * @throws MoloniActivatorException
     */
    private function installDatabase(): void
    {
        $installSqlFiles = glob($this->module->getLocalPath() . '/src/Activators/sql/*.sql');

        if (empty($installSqlFiles)) {
            throw new MoloniActivatorException('Error loading SQL files!');
        }

        $database = \Db::getInstance();

        foreach ($installSqlFiles as $sqlFile) {
            $sqlStatements = $this->getSqlStatements($sqlFile);

            try {
                $database->execute($sqlStatements);
            } catch (\Exception $exception) {
                $parts = explode('/', $sqlFile);
                $msg = 'Error executing operation from %s!';

                throw new MoloniActivatorException(sprintf($msg, end($parts)));
            }
        }
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
        $sqlStatements = \Tools::file_get_contents($fileName);

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

        foreach (\Language::getLanguages() as $lang) {
            $isoCode = $lang['iso_code'];
            $idLang = $lang['id_lang'];

            $translatedNames[$idLang] = $names[$isoCode] ?? reset($names);
        }

        return $translatedNames;
    }

    private function registerHooks(): void
    {
        foreach ($this->hooks as $hookName) {
            $this->module->registerHook($hookName);
        }
    }
}
