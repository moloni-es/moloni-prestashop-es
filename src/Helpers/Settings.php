<?php

namespace Moloni\Helpers;

use Db;
use PrestaShopDatabaseException;

class Settings
{
    /**
     * @var array array form: [label => value]
     */
    private static $cachedSettings;

    /**
     * Returns a settings value from cache
     *
     * @param string $settingName name of the setting
     *
     * @return bool|string returns the cached string value or false if does not exist
     */
    public static function get($settingName)
    {
        if (!isset(self::$cachedSettings[$settingName])) {
            self::checkCache();
        }

        return isset(self::$cachedSettings[$settingName]) ? self::$cachedSettings[$settingName] : false;
    }

    /**
     * Checks if the cache array has values, if not fills it
     *
     * @return bool return the value from fillCache() function
     *
     * @throws PrestaShopDatabaseException
     */
    public static function checkCache()
    {
        if (empty(self::$cachedSettings)) {
            return self::fillCache();
        }

        return true;
    }

    /**
     * Fill the cache array with database values
     *
     * @return bool return true or false depending on the database having values
     *
     * @throws PrestaShopDatabaseException
     */
    public static function fillCache()
    {
        $dataBase = Db::getInstance();
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'moloni_settings WHERE store_id=1';
        $queryResult = $dataBase->executeS($query);

        if (empty($queryResult)) {
            return false;
        }

        foreach ($queryResult as $aux) {
            self::$cachedSettings[$aux['label']] = $aux['value'];
        }

        return true;
    }

    /**
     * Returns all the values from cache
     * (example): ['Type' => 'Bill', 'Status' => 'Draft', ...]
     *
     * @return array|bool returns the cached array with settings data or false if empty
     *
     * @throws PrestaShopDatabaseException
     */
    public static function getAll()
    {
        if (!isset(self::$cachedSettings)) {
            self::checkCache();
        }

        return isset(self::$cachedSettings) ? self::$cachedSettings : false;
    }

    /**
     * Saves an value in cache
     *
     * @param array $setting array with the form (example): ['label' => 'Type', 'value' => 'Bill']
     */
    public static function set($setting)
    {
        self::$cachedSettings[$setting['label']] = $setting['value'];
    }

    /**
     * Sets the cache with the array from param
     *
     * @param array $settingsArray array with the form (example): ['Type' => 'Bill', 'Status' => 'Draft', ...]
     */
    public static function setAll($settingsArray)
    {
        self::$cachedSettings = $settingsArray;
    }
}
