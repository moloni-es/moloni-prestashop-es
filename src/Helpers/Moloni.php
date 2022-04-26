<?php

namespace Moloni\Helpers;

use Db;

class Moloni
{
    /**
     * @var array array form: [label => value]
     */
    private static $cachedCompany;

    /**
     * Returns a company value from cache
     *
     * @param string $valueName name of the company value
     *
     * @return bool|string returns the cached string value or false if does not exist
     */
    public static function get(string $valueName)
    {
        if (!isset(self::$cachedCompany[$valueName])) {
            self::checkCache();
        }

        return isset(self::$cachedCompany[$valueName]) ? self::$cachedCompany[$valueName] : false;
    }

    /**
     * Checks if the cache array has values, if not fills it
     *
     * @return bool return the value from fillCache() function
     */
    public static function checkCache()
    {
        if (empty(self::$cachedCompany)) {
            return self::fillCache();
        }

        return true;
    }

    /**
     * Fill the cache array with database values
     *
     * @return bool return true or false depending on the database having values
     */
    public static function fillCache()
    {
        $dataBase = Db::getInstance();
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'moloni_app ORDER BY id DESC';
        $queryResult = $dataBase->getRow($query, false);

        if (empty($queryResult)) {
            return false;
        }

        self::$cachedCompany = $queryResult;

        return true;
    }

    /**
     * Returns all the values from cache
     *
     * @return array|bool returns the cached array with company data or false if empty
     */
    public static function getAll()
    {
        if (!isset(self::$cachedCompany)) {
            self::checkCache();
        }

        return isset(self::$cachedCompany) ? self::$cachedCompany : false;
    }

    /**
     * Saves an value in cache
     *
     * @param array $value array with the form (example): ['label' => 'company_id', 'value' => '470']
     */
    public static function set($value): void
    {
        self::$cachedCompany[$value['label']] = $value['value'];
    }

    /**
     * Sets the cache with the array from param
     *
     * @param array $valueArray array with the form (example): ['company_id' => '470', 'client_id' => '1', ...]
     */
    public static function setAll($valueArray): void
    {
        self::$cachedCompany = $valueArray;
    }
}
